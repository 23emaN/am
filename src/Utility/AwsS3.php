<?php
namespace App\Utility;

use Aws\S3\S3Client;
use Aws\Credentials\Credentials;
use Exception;

class AwsS3
{
    private static function getClient(): S3Client
    {
        return new S3Client([
            'version' => 'latest',
            'region' => $_ENV['AWS_DEFAULT_REGION'] ?? 'ap-southeast-2',
            'credentials' => new Credentials(
                $_ENV['AWS_ACCESS_KEY_ID'] ?? '',
                $_ENV['AWS_SECRET_ACCESS_KEY'] ?? ''
            )
        ]);
    }

    private static function getBucket(): string
    {
        return $_ENV['AWS_BUCKET'] ?? 'cpdth-storage';
    }

    public static function generateFileKey($folder = null, $length = 32)
    {
        $x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomStr = substr(str_shuffle(str_repeat($x, ceil($length / strlen($x)))), 1, $length);
        if (empty($folder)) {
            return $randomStr;
        } else {
            return $folder . "/" . $randomStr;
        }
    }

    public static function uploadFileDirectly($file, $is_public = true, $folder = null, $filename = null)
    {
        try {
            if (!isset($file['tmp_name']) || !file_exists($file['tmp_name'])) {
                throw new Exception("Invalid file");
            }

            $s3Client = self::getClient();
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            
            if ($filename === null) {
                $upload_path = self::generateFileKey($folder) . "." . $ext;
            } else {
                $upload_path = ($folder != "" ? $folder . "/" : "") . $filename . "." . $ext;
            }
            
            $bucket = self::getBucket();

            $data = [
                'Bucket' => $bucket,
                'Key' => $upload_path,
                'Body' => fopen($file['tmp_name'], 'r'),
                'ContentType' => $file['type'],
            ];
            
            if ($is_public) {
                $data['ACL'] = 'public-read';
            }

            $result = $s3Client->putObject($data);

            return [
                'url' => $result['ObjectURL'],
                'path' => $upload_path,
                'is_public' => $is_public
            ];
        } catch (Exception $exception) {
            return [
                'error' => $exception->getMessage(),
                'url' => null,
                'path' => null,
                'is_public' => $is_public
            ];
        }
    }

    public static function uploadFileByPath($path, $is_public = true, $folder = null, $filename = null)
    {
        try {
            $s3Client = self::getClient();
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            
            if ($filename === null) {
                $upload_path = self::generateFileKey($folder) . "." . $ext;
            } else {
                $upload_path = ($folder != "" ? $folder . "/" : "") . $filename . "." . $ext;
            }
            
            $data = [
                'Bucket' => self::getBucket(),
                'Key' => $upload_path,
                'SourceFile' => $path
            ];
            
            if ($is_public) {
                $data['ACL'] = 'public-read';
            }
            
            $result = $s3Client->putObject($data);
            
            return [
                'url' => $result['ObjectURL'],
                'path' => $upload_path,
                'is_public' => $is_public
            ];
        } catch (Exception $exception) {
            return [
                'url' => null,
                'path' => null,
                'is_public' => $is_public,
                'error' => $exception->getMessage()
            ];
        }
    }

    public static function uploadFileByPathOldName($path, $is_public = true, $folder = null, $filename = null)
    {
        try {
            $s3Client = self::getClient();
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            
            if ($filename === null) {
                $upload_path = ($folder) . "." . $ext;
            } else {
                $upload_path = ($folder != "" ? $folder . "/" : "") . $filename . "." . $ext;
            }
            
            $data = [
                'Bucket' => self::getBucket(),
                'Key' => $upload_path,
                'SourceFile' => $path
            ];
            
            if ($is_public) {
                $data['ACL'] = 'public-read';
            }
            
            $result = $s3Client->putObject($data);
            
            return [
                'url' => $result['ObjectURL'],
                'path' => $upload_path,
                'is_public' => $is_public
            ];
        } catch (Exception $exception) {
            return [
                'url' => null,
                'path' => null,
                'is_public' => $is_public,
                'error' => $exception->getMessage()
            ];
        }
    }

    public static function getFileUrl($path, $expire_in = '+30 minutes')
    {
        try {
            $s3Client = self::getClient();
            $cmd = $s3Client->getCommand('GetObject', [
                'Bucket' => self::getBucket(),
                'Key' => $path,
                'ACL' => 'public-read'
            ]);
            $request = $s3Client->createPresignedRequest($cmd, $expire_in);
            return (string)$request->getUri();
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function checkExistByBigsara($path)
    {
        try {
            $s3Client = self::getClient();
            $exists = $s3Client->doesObjectExist(self::getBucket(), $path);
            return $exists ? "[พบ]\n" : "[ไม่พบ]\n";
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }

    public static function deleteFile($path)
    {
        try {
            $s3Client = self::getClient();
            $s3Client->deleteObject([
                'Bucket' => self::getBucket(),
                'Key' => $path
            ]);
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }

    public static function deleteFileByURL($url)
    {
        try {
            $s3Client = self::getClient();
            $url_data = parse_url($url);
            $path = $url_data['path'];
            $s3Client->deleteObject([
                'Bucket' => self::getBucket(),
                'Key' => ltrim($path, '/')
            ]);
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }
}
