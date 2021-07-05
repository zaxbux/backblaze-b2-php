<?php

namespace Zaxbux\BackblazeB2\Object\Key;

final class CapabilityType {
	public const BYPASS_GOVERNANCE       = 'bypassGovernance';
	public const DELETE_BUCKETS          = 'deleteBuckets';
	public const DELETE_FILES            = 'deleteFiles';
	public const DELETE_KEYS             = 'deleteKeys';
	public const LIST_ALL_BUCKET_NAMES   = 'listAllBucketNames';
	public const LIST_BUCKETS            = 'listBuckets';
	public const LIST_FILES              = 'listFiles';
	public const LIST_KEYS               = 'listKeys';
	public const READ_BUCKET_ENCRYPTION  = 'readBucketEncryption';
	public const READ_BUCKET_RETENTIONS  = 'readBucketRetentions';
	public const READ_BUCKETS            = 'readBuckets';
	public const READ_FILE_LEGAL_HOLDS   = 'readFileLegalHolds';
	public const READ_FILE_RETENTIONS    = 'readFileRetentions';
	public const READ_FILES              = 'readFiles';
	public const SHARE_FILES             = 'shareFiles';
	public const WRITE_BUCKET_ENCRYPTION = 'writeBucketEncryption';
	public const WRITE_BUCKET_RETENTIONS = 'writeBucketRetentions';
	public const WRITE_BUCKETS           = 'writeBuckets';
	public const WRITE_FILE_LEGAL_HOLDS  = 'writeFileLegalHolds';
	public const WRITE_FILE_RETENTIONS   = 'writeFileRetentions';
	public const WRITE_FILES             = 'writeFiles';
	public const WRITE_KEYS              = 'writeKeys';
}