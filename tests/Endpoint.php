<?php

namespace tests;

final class Endpoint
{
	public const AUTHORIZE_ACCOUNT = '/b2_authorize_account';
	public const CANCEL_LARGE_FILE = '/b2_cancel_large_file';
	public const COPY_FILE = '/b2_copy_file';
	public const COPY_PART = '/b2_copy_part';
	public const CREATE_BUCKET = '/b2_create_bucket';
	public const CREATE_KEY = '/b2_create_key';
	public const DELETE_BUCKET = '/b2_delete_bucket';
	public const DELETE_FILE_VERSION = '/b2_delete_file_version';
	public const DELETE_KEY = '/b2_delete_key';
	public const DOWNLOAD_FILE_BY_ID = '/b2_download_file_by_id';
	public const DOWNLOAD_FILE_BY_NAME = '/b2_download_file_by_name';
	public const FINISH_LARGE_FILE = '/b2_finish_large_file';
	public const GET_DOWNLOAD_AUTHORIZATION = '/b2_get_download_authorization';
	public const GET_FILE_INFO = '/b2_get_file_info';
	public const GET_UPLOAD_PART_URL = '/b2_get_upload_part_url';
	public const GET_UPLOAD_URL = '/b2_get_upload_url';
	public const HIDE_FILE = '/b2_hide_file';
	public const LIST_BUCKETS = '/b2_list_buckets';
	public const LIST_FILE_NAMES = '/b2_list_file_names';
	public const LIST_FILE_VERSIONS = '/b2_list_file_versions';
	public const LIST_KEYS = '/b2_list_keys';
	public const LIST_PARTS = '/b2_list_parts';
	public const LIST_UNFINISHED_LARGE_FILES = '/b2_list_unfinished_large_files';
	public const START_LARGE_FILE = '/b2_start_large_file';
	public const UPDATE_BUCKET = '/b2_update_bucket';
	public const UPDATE_FILE_LEGAL_HOLD = '/b2_update_file_legal_hold';
	public const UPDATE_FILE_RETENTION = '/b2_update_file_retention';
	public const UPLOAD_FILE = '/b2_upload_file';
	public const UPLOAD_PART = '/b2_upload_part';
}
