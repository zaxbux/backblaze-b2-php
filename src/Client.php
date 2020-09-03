<?php

namespace Zaxbux\BackBlazeB2;

use Zaxbux\BackblazeB2\Exception\NotFoundException;
use Zaxbux\BackblazeB2\Exception\ValidationException;
use Zaxbux\BackblazeB2\Http\Client as HttpClient;

class Client
{
    const METADATA_DIRECTIVE_COPY    = "COPY";
    const METADATA_DIRECTIVE_REPLACE = "REPLACE";

    protected $accountId;
    protected $applicationKeyId;
    protected $applicationKey;
    protected $allowed;

    protected $authToken;
    protected $apiUrl;
    protected $downloadUrl;
    protected $recommendedPartSize;
    protected $absoluteMinimumPartSize;

    protected $client;

    /**
     * Client constructor. Accepts the account ID, application key and an optional array of options.
     *
     * @param $accountId
     * @param $applicationKey
     * @param array $options
     * @param AuthCacheInterface $authCache
     */
    public function __construct($applicationKeyId, $applicationKey, array $options = [], $authCache = null)
    {
        $this->applicationKeyId = $applicationKeyId;
        $this->applicationKey = $applicationKey;

        if (isset($options['client'])) {
            $this->client = $options['client'];
        } else {
            $this->client = new HttpClient(['exceptions' => false]);
        }

        $this->authorizeAccount($authCache);
    }

    /**
     * Authorize the B2 account in order to get an auth token and API/download URLs.
     *
     * @param AuthCacheINterface $authCache
     * @throws \Exception
     */
    protected function authorizeAccount($authCache = null)
    {
        $basic = base64_encode($this->applicationKeyId . ":" . $this->applicationKey);

        $authData = [];

        if ($authCache instanceof AuthCacheInterface) {
            $authData = $authCache->cachedB2Auth($basic);
        }

        if (empty($authData)) {
            $authData = $this->client->request('GET', 'https://api.backblazeb2.com/b2api/v2/b2_authorize_account', [
                'headers' => [
                    'Authorization' => 'Basic ' . $basic
                ]
            ]);

            if (!empty($authData) && !empty($authCache)) {
                $authCache->cacheB2Auth($basic, $authData);
            }
        }

        if (!empty($authData)) {
            $this->accountId               = $authData['accountId'];
            $this->authToken               = $authData['authorizationToken'];
            $this->apiUrl                  = $authData['apiUrl'].'/b2api/v2';
            $this->downloadUrl             = $authData['downloadUrl'];
            $this->allowed                 = $authData['allowed'];
            $this->recommendedPartSize     = $authData['recommendedPartSize'];
            $this->absoluteMinimumPartSize = $authData['absoluteMinimumPartSize'];
        }
    }

    /**
     * Cancel the upload of a large file, and deletes all of the parts that have been uploaded.
     * 
     * @param array $options
     * @return array
     * @throws ValidationException
     */
    public function cancelLargeFile(array $options)
    {
        if (!isset($options['FileId'])) {
            throw new ValidationException('FileId is required');
        }

        $response = $this->client->request('POST', $this->apiUrl.'/b2_cancel_large_file', [
            'headers' => [
                'Authorization' => $this->authToken,
            ],
            'json' => [
                'fileId' => $options['FileId'],
            ],
        ]);

        return $response;
    }

    /**
     * Get the capabilities, bucket restrictions, and prefix restrictions.
     * 
     * @return array
     */
    public function getPermissions()
    {
        return $this->allowed;
    }

    /**
     * The recomended part size for each part of a large file. It is recomended to use this part size for optimal performance.
     * 
     * @return int
     */
    public function getRecommendedPartSize()
    {
        return $this->recommendedPartSize;
    }

    /**
     * The smallest possible size of a part of a large file (except the last one). Upload performance may be impacted if you use this value.
     * 
     * @return int
     */
    public function getAbsoluteMinimumPartSize()
    {
        return $this->absoluteMinimumPartSize;
    }

    /**
     * Create a bucket with the given name and type.
     *
     * @param array $options
     * @return Bucket
     * @throws ValidationException
     */
    public function createBucket(array $options)
    {
        if (!in_array($options['BucketType'], [Bucket::TYPE_PUBLIC, Bucket::TYPE_PRIVATE])) {
            throw new ValidationException(
                sprintf('Bucket type must be %s or %s', Bucket::TYPE_PRIVATE, Bucket::TYPE_PUBLIC)
            );
        }

        $response = $this->client->request('POST', $this->apiUrl.'/b2_create_bucket', [
            'headers' => [
                'Authorization' => $this->authToken,
            ],
            'json' => [
                'accountId' => $this->accountId,
                'bucketName' => $options['BucketName'],
                'bucketType' => $options['BucketType']
            ]
        ]);

        return new Bucket($response['bucketId'], $response['bucketName'], $response['bucketType']);
    }

    /**
     * Creates a new application key.
     * 
     * @param array $options
     * @return array
     * @throws ValidationException
     */
    public function createKey(array $options)
    {
        if (!\in_array('writeKeys', $this->allowed)) {
            throw new ValidationException('writeKeys capability required.');
        }

        if (!isset($options['Capabilities']) || gettype($options['Capabilities']) != 'array' || count($options['Capabilities']) == 0) {
            throw new ValidationException('Capabilities is required and must be an array with at least one valid item.');
        }

        if (!isset($options['KeyName'])) {
            throw new ValidationException('KeyName is required');
        }

        $json = [
            'accountId' => $this->accountId,
            'capabilities' => $options['Capabilities'],
            'keyName' => $options['KeyName'],
        ];

        if (isset($options['ValidDurationInSeconds'])) {
            $json['validDurationInSeconds'] = $options['ValidDurationInSeconds'];
        }

        if (isset($options['BucketId'])) {
            $json['bucketId'] = $options['BucketId'];
        }

        if (isset($options['FileNamePrefix'])) {
            $json['namePrefix'] = $options['FileNamePrefix'];
        }

        $response = $this->client->request('POST', $this->apiUrl.'/b2_create_key', [
            'headers' => [
                'Authorization' => $this->authToken()
            ],
            'json' => $json,
        ]);

        return $response;
    }

    /**
     * Updates the type attribute of a bucket by the given ID.
     *
     * @param array $options
     * @return Bucket
     * @throws ValidationException
     */
    public function updateBucket(array $options)
    {
        if (!in_array($options['BucketType'], [Bucket::TYPE_PUBLIC, Bucket::TYPE_PRIVATE])) {
            throw new ValidationException(
                sprintf('Bucket type must be %s or %s', Bucket::TYPE_PRIVATE, Bucket::TYPE_PUBLIC)
            );
        }

        if (!isset($options['BucketId']) && isset($options['BucketName'])) {
            $options['BucketId'] = $this->getBucketIdFromName($options['BucketName']);
        }

        $response = $this->client->request('POST', $this->apiUrl.'/b2_update_bucket', [
            'headers' => [
                'Authorization' => $this->authToken,
            ],
            'json' => [
                'accountId' => $this->accountId,
                'bucketId' => $options['BucketId'],
                'bucketType' => $options['BucketType']
            ]
        ]);

        return new Bucket($response['bucketId'], $response['bucketName'], $response['bucketType']);
    }

    /**
     * Generates an authorization token that can be used to download files
     * with the specified prefix from a private B2 bucket.
     * 
     * @param array $options
     * @return array
     * @throws ValidationException
     */
    public function getDownloadAuthorization(array $options) {
        if (!isset($options['BucketId'])) {
            throw new ValidationException('BucketId is required');
        }

        if (!isset($options['FileNamePrefix'])) {
            throw new ValidationException('FileNamePrefix is required');
        }

        if (!isset($options['ValidDurationInSeconds'])) {
            throw new ValidationException('ValidDurationInSeconds is required');
        }

        if (isset($options['ContentLanguage'])) {
            $json['b2ContentLanguage'] = $options['ContentLanguage'];
        }

        if (isset($options['Expires'])) {
            $json['b2Expires'] = $options['Expires'];
        }

        if (isset($options['CacheControl'])) {
            $json['b2CacheControl'] = $options['CacheControl'];
        }

        if (isset($options['ContentEncoding'])) {
            $json['b2ContentEncoding'] = $options['ContentEncoding'];
        }

        if (isset($options['ContentType'])) {
            $json['b2ContentType'] = $options['ContentType'];
        }

        $json = [
            'bucketId'               => $options['BucketId'],
            'fileNamePrefix'         => $options['FileNamePrefix'],
            'validDurationInSeconds' => $options['ValidDurationInSeconds'],
        ];

        $response = $this->client->request('POST', $this->apiUrl.'/b2_get_download_authorization', [
            'headers' => [
                'Authorization' => $this->authToken,
            ],
            'json' => $json
        ]);

        return $response['authorizationToken'];
    }

    /**
     * Hides a file so that downloading by name will not find the file,
     * but previous versions of the file are still stored.
     * 
     * @param array $options
     * @return bool
     * @throws ValidationException
     * @throws NotFoundException
     */
    public function hideFile(array $options) {

        if (!isset($options['FileId']) && isset($options['BucketName']) && isset($options['FileName'])) {
            $file = $this->getFileIdFromBucketAndFileName($options['BucketName'], $options['FileName']);

            if (!$file) {
                throw new NotFoundException();
            }

            $options['FileId'] = $file->getId();
            $options['BucketId'] = $file->getBucketId();
        }

        if (!isset($options['BucketId']) && isset($options['BucketName'])) {
            $options['BucketId'] = $this->getBucketIdFromName($options['BucketName']);
        }

        if (!isset($options['BucketId'])) {
            throw new ValidationException('BucketId or BucketName is required.');
        }

        $response = $this->client->request('POST', $this->apiUrl.'/b2_hide_file', [
            'headers' => [
                'Authorization' => $this->authToken,
            ],
            'json' => [
                'bucketId' => $options['BucketId'],
                'fileId'   => $options['FileId']
            ]
        ]);

        return $response['action'] == 'hide';
    }

    /**
     * Returns a list of bucket objects representing the buckets on the account.
     *
     * @param array $options Additional options to pass to the API
     * @return array
     */
    public function listBuckets(array $options = [])
    {
        $buckets = [];

        $options = array_replace_recursive($options, [
            'headers' => [
                'Authorization' => $this->authToken
            ],
            'json' => [
                'accountId' => $this->accountId
            ]
        ]);

        $response = $this->client->request('POST', $this->apiUrl.'/b2_list_buckets', $options);

        foreach ($response['buckets'] as $bucket) {
            $buckets[] = new Bucket($bucket['bucketId'], $bucket['bucketName'], $bucket['bucketType']);
        }

        return $buckets;
    }
    
    /**
     * Lists information about large file uploads that have been started, but have not been finished or canceled.
     * 
     * @param array $options
     * @return array
     * @throws ValidationException
     */
    public function listUnfinishedLargeFiles(array $options)
    {
        if (!isset($options['BucketId'])) {
            throw new ValidationException('BucketId is required');
        }

        $json = [
            'bucketId' => $options['bucketId']
        ];

        if (isset($options['NamePrefix'])) {
            $json['namePrefix'] = $options['NamePrefix'];
        }

        if (isset($options['StartFileId'])) {
            $json['startFileId'] = $options['StartFileId'];
        }

        if (isset($options['MaxFileCount'])) {
            $json['maxFileCount'] = $options['MaxFileCount'];
        }

        $response = $this->client->request('POST', $this->apiUrl.'/b2_list_unfinished_large_files', [
            'headers' => [
                'Authorization' => $this->authToken,
            ],
            'json' => $json,
        ]);

        $files = [];

        foreach ($response['files'] as $file) {
            $files[] = new File(
                $response['fileId'],
                $response['fileName'],
                $response['contentSha1'],
                $response['contentLength'],
                $response['contentType'],
                $response['fileInfo'],
                $response['bucketId'],
                $response['action'],
                $response['uploadTimestamp']
            );
        }

        $response['files'] = $files;

        return $response;
    }

    /**
     * Deletes the bucket identified by its ID.
     *
     * @param array $options
     * @return bool
     */
    public function deleteBucket(array $options)
    {
        if (!isset($options['BucketId']) && isset($options['BucketName'])) {
            $options['BucketId'] = $this->getBucketIdFromName($options['BucketName']);
        }

        $this->client->request('POST', $this->apiUrl.'/b2_delete_bucket', [
            'headers' => [
                'Authorization' => $this->authToken
            ],
            'json' => [
                'accountId' => $this->accountId,
                'bucketId' => $options['BucketId']
            ]
        ]);

        return true;
    }

    /**
     * Uploads a file to a bucket and returns a File object.
     *
     * @param array $options
     * @return File
     */
    public function upload(array $options)
    {
        // Clean the path if it starts with /.
        if (substr($options['FileName'], 0, 1) === '/') {
            $options['FileName'] = ltrim($options['FileName'], '/');
        }

        if (!isset($options['BucketId']) && isset($options['BucketName'])) {
            $options['BucketId'] = $this->getBucketIdFromName($options['BucketName']);
        }

        // Retrieve the URL that we should be uploading to.
        $response = $this->client->request('POST', $this->apiUrl.'/b2_get_upload_url', [
            'headers' => [
                'Authorization' => $this->authToken
            ],
            'json' => [
                'bucketId' => $options['BucketId']
            ]
        ]);

        $uploadEndpoint = $response['uploadUrl'];
        $uploadAuthToken = $response['authorizationToken'];

        if (is_resource($options['Body'])) {
            // We need to calculate the file's hash incrementally from the stream.
            $context = hash_init('sha1');
            hash_update_stream($context, $options['Body']);
            $hash = hash_final($context);

            // Similarly, we have to use fstat to get the size of the stream.
            $size = fstat($options['Body'])['size'];
            
            // Rewind the stream before passing it to the HTTP client.
            rewind($options['Body']);
        } else {
            // We've been given a simple string body, it's super simple to calculate the hash and size.
            $hash = sha1($options['Body']);
            $size = mb_strlen($options['Body']);
        }

        if (!isset($options['FileLastModified'])) {
            $options['FileLastModified'] = round(microtime(true) * 1000);
        }

        if (!isset($options['FileContentType'])) {
            $options['FileContentType'] = 'b2/x-auto';
        }

        $response = $this->client->request('POST', $uploadEndpoint, [
            'headers' => [
                'Authorization' => $uploadAuthToken,
                'Content-Type' => $options['FileContentType'],
                'Content-Length' => $size,
                'X-Bz-File-Name' => $options['FileName'],
                'X-Bz-Content-Sha1' => $hash,
                'X-Bz-Info-src_last_modified_millis' => $options['FileLastModified']
            ],
            'body' => $options['Body']
        ]);

        return new File(
            $response['fileId'],
            $response['fileName'],
            $response['contentSha1'],
            $response['contentLength'],
            $response['contentType'],
            $response['fileInfo']
        );
    }

    /**
     * Download a file from a B2 bucket.
     *
     * @param array $options
     * @return bool|mixed|string
     */
    public function download(array $options)
    {
        $requestUrl = null;
        $requestOptions = [
            'headers' => [
                'Authorization' => $this->authToken
            ],
            'sink' => isset($options['SaveAs']) ? $options['SaveAs'] : null
        ];

        if (isset($options['FileId'])) {
            $requestOptions['query'] = ['fileId' => $options['FileId']];
            $requestUrl = $this->downloadUrl.'/b2api/v2/b2_download_file_by_id';
        } else {
            if (!isset($options['BucketName']) && isset($options['BucketId'])) {
                $options['BucketName'] = $this->getBucketNameFromId($options['BucketId']);
            }

            $requestUrl = sprintf('%s/file/%s/%s', $this->downloadUrl, $options['BucketName'], $options['FileName']);
        }

        if (isset($options['Stream'])) {
            $requestOptions['stream'] = $options['Stream'];
            $response = $this->client->request('GET', $requestUrl, $requestOptions, false, false);
        } else {
            $response = $this->client->request('GET', $requestUrl, $requestOptions, false);
        }

        return isset($options['SaveAs']) ? true : $response;
    }

    /**
     * Copy a file in a B2 bucket
     * 
     * @param array $options
     * @return File
     * @throws ValidationException
     */
    public function copyFile(array $options)
    {
        if (!isset($options['SourceFileId']) && isset($options['BucketName']) && isset($options['SourceFileName'])) {
            $file = $this->getFile([
                'BucketName' => $options['BucketName'],
                'FileName' => $options['SourceFileName']
            ]);
            $options['SourceFileId'] = $file->getId();
        }

        $json = [
            'sourceFileId' => $options['SourceFileId'],
            'fileName' => $options['FileName']
        ];

        if (isset($options['Range'])) {
            $json['range'] = $options['Range'];
        }

        if (isset($options['MetadataDirective'])) {
            if ($options['MetadataDirective'] == self::METADATA_DIRECTIVE_REPLACE && !isset($options['FileContentType'])) {
                $options['FileContentType'] = 'b2/x-auto';
            }
            if ($options['MetadataDirective'] == self::METADATA_DIRECTIVE_COPY && isset($options['FileContentType'])) {
                throw new ValidationException('FileContentType must not be provided when MetadataDirective is COPY');
            }
            if ($options['MetadataDirective'] == self::METADATA_DIRECTIVE_COPY && isset($options['FileInfo'])) {
                throw new ValidationException('FileInfo must not be provided when MetadataDirective is COPY');
            }
            $json['metadataDirective'] = $options['MetadataDirective'];
        }

        if (isset($options['FileContentType'])) {
            $json['contentType'] = $options['FileContentType'];
        }

        if (isset($options['FileInfo'])) {
            $json['fileInfo'] = $options['FileInfo'];
        }

        $response = $this->client->request('POST', $this->apiUrl.'/b2_copy_file', [
            'headers' => [
                'Authorization' => $this->authToken
            ],
            'json' => $json
        ]);
        
        return new File(
            $response['fileId'],
            $response['fileName'],
            $response['contentSha1'],
            $response['contentLength'],
            $response['contentType'],
            $response['fileInfo'],
            $response['bucketId'],
            $response['action'],
            $response['uploadTimestamp']
        );
    }

    /**
     * Create a new large file part by copying from an existing file
     * 
     * @param array $options
     * @return array
     */
    public function copyPart(array $options)
    {
        if (!isset($options['SourceFileId']) && isset($options['BucketName']) && isset($options['SourceFileName'])) {
            $file = $this->getFile([
                'BucketName' => $options['BucketName'],
                'FileName' => $options['SourceFileName']
            ]);
            $options['SourceFileId'] = $file->getId();
        }

        $json = [
            'sourceFileId' => $options['SourceFileId'],
            'largeFileId' => $options['LargeFileId'],
            'partNumber' => $options['PartNumber']
        ];

        if (isset($options['Range'])) {
            $json['range'] = $options['Range'];
        }

        $response = $this->client->request('POST', $this->apiUrl.'/b2_copy_part', [
            'headers' => [
                'Authorization' => $this->authToken
            ],
            'json' => $json
        ]);
        
        return [
            'partNumber' => $response['partNumber'],
            'file' => new File(
                $response['fileId'],
                $response['contentSha1'],
                $response['contentLength'],
                $response['uploadTimestamp']
            )
        ];
    }

    /**
     * Retrieve a collection of File objects representing the files stored inside a bucket.
     *
     * @param array $options
     * @return array
     */
    public function listFiles(array $options)
    {
        // if FileName is set, we only attempt to retrieve information about that single file.
        $fileName = !empty($options['FileName']) ? $options['FileName'] : null;

        $nextFileName = null;
        $maxFileCount = 1000;
        $files = [];

        if (!isset($options['BucketId']) && isset($options['BucketName'])) {
            $options['BucketId'] = $this->getBucketIdFromName($options['BucketName']);
        }

        if ($fileName) {
            $nextFileName = $fileName;
            $maxFileCount = 1;
        }

        // B2 returns, at most, 1000 files per "page". Loop through the pages and compile an array of File objects.
        while (true) {
            $json = [
                'bucketId' => $options['BucketId'],
                'startFileName' => $nextFileName,
                'maxFileCount' => $maxFileCount,
            ];

            if (isset($options['Prefix'])) {
                $json['prefix'] = $options['Prefix'];
            }

            if (isset($options['Delimiter'])) {
                $json['Delimiter'] = $options['Delimiter'];
            }

            $response = $this->client->request('POST', $this->apiUrl.'/b2_list_file_names', [
                'headers' => [
                    'Authorization' => $this->authToken
                ],
                'json' => $json
            ]);

            foreach ($response['files'] as $file) {
                // if we have a file name set, only retrieve information if the file name matches
                if (!$fileName || ($fileName === $file['fileName'])) {
                    $files[] = new File($file['fileId'], $file['fileName'], null, $file['contentLength']);
                }
            }

            if ($fileName || $response['nextFileName'] === null) {
                // We've got all the files - break out of loop.
                break;
            }

            $nextFileName = $response['nextFileName'];
        }

        return $files;
    }

    /**
     * Lists all of the versions of all of the files contained in one bucket,
     * in alphabetical order by file name,
     * and by reverse of date/time uploaded for versions of files with the same name.
     * 
     * @param array $options
     * @return array
     * @throws ValidationException
     */
    public function listFileVersions(array $options)
    {
        if (!isset($options['BucketId'])) {
            throw new ValidationException('BucketId is required');
        }

        if (isset($options['StartFileId']) && !isset($options['StartFileName'])) {
            throw new ValidationException('StartFileName is required if StartFileId is provided.');
        }

        $json = [
            'bucketId' => $options['BucketId']
        ];

        if (isset($options['StartFileName'])) {
            $json['startFileName'] = $options['StartFileName'];
        }

        if (isset($options['StartFileId'])) {
            $json['startFileId'] = $options['StartFileId'];
        }

        if (isset($options['MaxFileCount'])) {
            $json['maxFileCount'] = $options['MaxFileCount'];
        }

        if (isset($options['Prefix'])) {
            $json['prefix'] = $options['Prefix'];
        }

        if (isset($options['Delimiter'])) {
            $json['delimiter'] = $options['Delimiter'];
        }

        $response = $this->client->request('POST', $this->apiUrl, [
            'headers' => [
                'Authorization' => $this->authToken
            ],
            'json' => $json
        ]);

        $files = [];

        foreach ($response['files'] as $file) {
            $files[] = new File(
                $file['fileId'],
                $file['fileName'],
                $file['contentSha1'],
                $file['contentLength'],
                $file['contentType'],
                $file['fileInfo'],
                $file['bucketId'],
                $file['action'],
                $file['uploadTimestamp']
            );
        }

        $response['files'] = $files;

        return $response;
    }

    /**
     * Test whether a file exists in B2 for the given bucket.
     *
     * @param array $options
     * @return boolean
     */
    public function fileExists(array $options)
    {
        $files = $this->listFiles($options);

        return !empty($files);
    }


    /**
     * Returns a single File object representing a file stored on B2.
     *
     * @param array $options
     * @throws NotFoundException If no file id was provided and BucketName + FileName does not resolve to a file, a NotFoundException is thrown.
     * @return File
     */
    public function getFile(array $options)
    {
        if (!isset($options['FileId']) && isset($options['BucketName']) && isset($options['FileName'])) {
            $options['FileId'] = $this->getFileIdFromBucketAndFileName($options['BucketName'], $options['FileName']);

            if (!$options['FileId']) {
                throw new NotFoundException();
            }
        }

        $response = $this->client->request('POST', $this->apiUrl.'/b2_get_file_info', [
            'headers' => [
                'Authorization' => $this->authToken
            ],
            'json' => [
                'fileId' => $options['FileId']
            ]
        ]);

        return new File(
            $response['fileId'],
            $response['fileName'],
            $response['contentSha1'],
            $response['contentLength'],
            $response['contentType'],
            $response['fileInfo'],
            $response['bucketId'],
            $response['action'],
            $response['uploadTimestamp']
        );
    }

    /**
     * Deletes the file identified by ID from Backblaze B2.
     *
     * @param array $options
     * @return bool
     */
    public function deleteFile(array $options)
    {
        if (!isset($options['FileName'])) {
            $file = $this->getFile($options);

            $options['FileName'] = $file->getName();
        }

        if (!isset($options['FileId']) && isset($options['BucketName']) && isset($options['FileName'])) {
            $file = $this->getFile($options);

            $options['FileId'] = $file->getId();
        }

        $this->client->request('POST', $this->apiUrl.'/b2_delete_file_version', [
            'headers' => [
                'Authorization' => $this->authToken
            ],
            'json' => [
                'fileName' => $options['FileName'],
                'fileId' => $options['FileId']
            ]
        ]);

        return true;
    }

    /**
     * Maps the provided bucket name to the appropriate bucket ID.
     *
     * @param $name
     * @return null
     */
    protected function getBucketIdFromName($name)
    {
        $buckets = $this->listBuckets(['json' => ['bucketName' => $name]]);

        foreach ($buckets as $bucket) {
            if ($bucket->getName() === $name) {
                return $bucket->getId();
            }
        }

        return null;
    }

    /**
     * Maps the provided bucket ID to the appropriate bucket name.
     *
     * @param $id
     * @return null
     */
    protected function getBucketNameFromId($id)
    {
        $buckets = $this->listBuckets(['json' => ['bucketId' => $id]]);

        foreach ($buckets as $bucket) {
            if ($bucket->getId() === $id) {
                return $bucket->getName();
            }
        }

        return null;
    }

    protected function getFileIdFromBucketAndFileName($bucketName, $fileName)
    {
        $files = $this->listFiles([
            'BucketName' => $bucketName,
            'FileName' => $fileName,
        ]);

        foreach ($files as $file) {
            if ($file->getName() === $fileName) {
                return $file->getId();
            }
        }

        return null;
    }
}
