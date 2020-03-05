<?php

/**
 * @package    JD Builder
 * @author     Team Joomdev <info@joomdev.com>
 * @copyright  2020 www.joomdev.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace JDPageBuilder;

// No direct access
defined('_JEXEC') or die('Restricted access');

define('MEDIA_PATH', JPATH_ROOT . '/images/');

\jimport('joomla.filesystem.file');
\jimport('joomla.filesystem.folder');
\jimport('joomla.filesystem.element');

class Media
{

   public static $unwanted_ext = [
      '.DS_Store',
      '.localized',
      'Thumbs.db',
      'error_log'
   ];

   public function __construct()
   {
      define('MEDIA_URL', \JURI::root() . 'images/');
   }

   public function get($folder)
   {

      $current = $folder;
      $basePath = MEDIA_PATH;
      $images = array();
      $folders = array();
      $docs = array();
      $videos = array();

      $fileList = false;
      $folderList = false;
      if (strpos($current, '..') !== false) {
         throw new \Exception("Directory Not Found");
      }
      $path = $basePath . $current;

      if (file_exists($path)) {
         $fileList = $this->files($path);
         $folderList = $this->folders($path);
      }

      // Iterate over the files if they exist
      if ($fileList !== false) {
         $tmpBaseObject = new \stdClass();

         foreach ($fileList as $file) {
            if (is_file($path . '/' . $file) && substr($file, 0, 1) != '.' && strtolower($file) !== 'index.html') {
               $tmp = clone $tmpBaseObject;
               $tmp->name = $file;
               $tmp->title = $file;
               $tmp->path = str_replace(DIRECTORY_SEPARATOR, '/', str_replace($this->clean($basePath), '', $this->clean($path . '/' . $file)));
               $tmp->url = MEDIA_URL . $tmp->path;
               //$tmp->size = filesize($basePath . $tmp->path);

               $ext = strtolower($this->getExt($file));

               switch ($ext) {
                     // Image
                  case 'jpg':
                  case 'png':
                  case 'gif':
                  case 'xcf':
                  case 'odg':
                  case 'bmp':
                  case 'jpeg':
                  case 'svg':
                  case 'webp':
                  case 'ico':
                  case 'tiff':
                     $info = @getimagesize($tmp->path);
                     $tmp->width = @$info[0];
                     $tmp->height = @$info[1];
                     $tmp->type = @$info[2];
                     $tmp->mime = @$info['mime'];
                     $tmp->media_type = 'image';

                     if (($info[0] > 60) || ($info[1] > 60)) {
                        $dimensions = $this->imageResize($info[0], $info[1], 60);
                        $tmp->width_60 = $dimensions[0];
                        $tmp->height_60 = $dimensions[1];
                     } else {
                        $tmp->width_60 = $tmp->width;
                        $tmp->height_60 = $tmp->height;
                     }

                     if (($info[0] > 16) || ($info[1] > 16)) {
                        $dimensions = $this->imageResize($info[0], $info[1], 16);
                        $tmp->width_16 = $dimensions[0];
                        $tmp->height_16 = $dimensions[1];
                     } else {
                        $tmp->width_16 = $tmp->width;
                        $tmp->height_16 = $tmp->height;
                     }

                     $images[] = $tmp;
                     break;

                     // Video
                  case 'mp4':
                  case 'webm':
                  case 'ogg':
                     $tmp->icon_32 = 'media/mime-icon-32/' . $ext . '.png';
                     $tmp->icon_16 = 'media/mime-icon-16/' . $ext . '.png';
                     $videos[] = $tmp;
                     $tmp->media_type = 'video';
                     break;

                     // Non-image document
                  default:
                     $tmp->icon_32 = 'media/mime-icon-32/' . $ext . '.png';
                     $tmp->icon_16 = 'media/mime-icon-16/' . $ext . '.png';
                     $docs[] = $tmp;
                     $tmp->media_type = 'document';
                     break;
               }
            }
         }
      }

      // Iterate over the folders if they exist
      if ($folderList !== false) {
         $tmpBaseObject = new \stdClass();

         foreach ($folderList as $folder) {
            $tmp = clone $tmpBaseObject;
            $tmp->name = basename($folder);
            $tmp->path = str_replace(DIRECTORY_SEPARATOR, '/', str_replace($this->clean($basePath), '', $this->clean($path . '/' . $folder)));
            $count = $this->countFiles($tmp->path);
            $tmp->files = $count[0];
            $tmp->folders = $count[1];
            $tmp->media_type = 'folder';

            $folders[] = $tmp;
         }
      }

      $path = str_replace(DIRECTORY_SEPARATOR, '/', str_replace($this->clean($basePath), '', $this->clean($path)));

      $list = array('folders' => $folders, 'docs' => $docs, 'images' => $images, 'videos' => $videos, 'current' => $path, 'back' => $this->backlink($path), 'breadcrumb' => $this->breadcrumb($path));

      return $list;
   }

   public function breadcrumb($path)
   {
      $return = [];
      $path = str_replace(MEDIA_PATH, '', $path);

      while (substr($path, -1) == '/') {
         $path = substr($path, 0, -1);
      }

      while (1) {
         if (empty($path)) {
            break;
         }
         $name = pathinfo($path, PATHINFO_BASENAME);
         if ($path == ".") {
            $path = $name;
         }
         $return[] = ['path' => $path, 'name' => $name];
         if ($path == $name) {
            break;
         }
         $path = pathinfo($path, PATHINFO_DIRNAME);
      }

      $return[] = ['path' => '', 'name' => ''];
      return array_reverse($return);
   }

   function backlink($path)
   {
      $return = str_replace(MEDIA_PATH, '', dirname($path));

      $base = substr(MEDIA_PATH, 0, -1);
      $return = str_replace($base, '', $return);
      return $return;
   }

   public function getExt($file)
   {
      $pathinfo = pathinfo($file);
      if (!isset($pathinfo['extension'])) {
         return "";
      }
      return $pathinfo['extension'];
   }

   public function imageResize($width, $height, $target)
   {
      /*
       * Takes the larger size of the width and height and applies the
       * formula accordingly. This is so this script will work
       * dynamically with any size image
       */
      if ($width > $height) {
         $percentage = ($target / $width);
      } else {
         $percentage = ($target / $height);
      }

      // Gets the new value and applies the percentage, then rounds the value
      $width = round($width * $percentage);
      $height = round($height * $percentage);

      return array($width, $height);
   }

   public function countFiles($dir)
   {
      $total_file = 0;
      $total_dir = 0;

      if (is_dir($dir)) {
         $d = dir($dir);

         while (($entry = $d->read()) !== false) {
            if ($entry[0] !== '.' && strpos($entry, '.html') === false && strpos($entry, '.php') === false && is_file($dir . DIRECTORY_SEPARATOR . $entry)) {
               $total_file++;
            }

            if ($entry[0] !== '.' && is_dir($dir . DIRECTORY_SEPARATOR . $entry)) {
               $total_dir++;
            }
         }

         $d->close();
      }

      return array($total_file, $total_dir);
   }

   public function files($path, $filter = '.', $recurse = false, $full = false, $exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX'), $excludefilter = array('^\..*', '.*~'), $naturalSort = false)
   {
      $path = $this->clean($path);
      // Is the path a folder?
      if (!is_dir($path)) {
         return false;
      }
      // Compute the excludefilter string
      if (count($excludefilter)) {
         $excludefilter_string = '/(' . implode('|', $excludefilter) . ')/';
      } else {
         $excludefilter_string = '';
      }
      // Get the files
      $arr = $this->_items($path, $filter, $recurse, $full, $exclude, $excludefilter_string, true);
      // Sort the files based on either natural or alpha method
      if ($naturalSort) {
         natsort($arr);
      } else {
         asort($arr);
      }
      return array_values($arr);
   }

   public function folders($path, $filter = '.', $recurse = false, $full = false, $exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX'), $excludefilter = array('^\..*'))
   {
      // Check to make sure the path valid and clean
      $path = $this->clean($path);
      // Compute the excludefilter string
      if (count($excludefilter)) {
         $excludefilter_string = '/(' . implode('|', $excludefilter) . ')/';
      } else {
         $excludefilter_string = '';
      }
      // Get the folders
      $arr = $this->_items($path, $filter, $recurse, $full, $exclude, $excludefilter_string, false);
      // Sort the folders
      asort($arr);
      return array_values($arr);
   }

   public function clean($path, $ds = DIRECTORY_SEPARATOR)
   {
      $path = trim($path);
      if (empty($path)) {
         $path = MEDIA_PATH;
      } else {
         // Remove double slashes and backslahses and convert all slashes and backslashes to DS
         $path = preg_replace('#[/\\\\]+#', $ds, $path);
      }
      return $path;
   }

   public function _items($path, $filter, $recurse, $full, $exclude, $excludefilter_string, $findfiles)
   {
      @set_time_limit(ini_get('max_execution_time'));
      $arr = array();
      // Read the source directory
      if (!($handle = @opendir($path))) {
         return $arr;
      }
      while (($file = readdir($handle)) !== false) {
         if ($file != '.' && $file != '..' && !in_array($file, $exclude) && (empty($excludefilter_string) || !preg_match($excludefilter_string, $file))) {
            // Compute the fullpath
            $fullpath = $path . '/' . $file;
            // Compute the isDir flag
            $isDir = is_dir($fullpath);
            if (($isDir xor $findfiles) && preg_match("/$filter/", $file)) {
               // (fullpath is dir and folders are searched or fullpath is not dir and files are searched) and file matches the filter
               if ($full) {
                  // Full path is requested
                  $arr[] = $fullpath;
               } else {
                  // Filename is requested
                  $arr[] = $file;
               }
            }
            if ($isDir && $recurse) {
               // Search recursively
               if (is_int($recurse)) {
                  // Until depth 0 is reached
                  $arr = array_merge($arr, $this->_items($fullpath, $filter, $recurse - 1, $full, $exclude, $excludefilter_string, $findfiles));
               } else {
                  $arr = array_merge($arr, $this->_items($fullpath, $filter, $recurse, $full, $exclude, $excludefilter_string, $findfiles));
               }
            }
         }
      }
      closedir($handle);
      return $arr;
   }

   public static function create()
   {
      $request = Builder::request();
      $directory = $request->get('dir', '', 'RAW');
      $directory = empty($directory) ? $directory : $directory . '/';
      $name = $request->get('name', '', 'RAW');
      if (empty($name)) {
         throw new \Exception("Invalid folder name.");
      }

      $dir = JPATH_SITE . '/images/' . $directory . $name;
      if (file_exists($dir)) {
         throw new \Exception("Folder <strong>$name</strong> already exists.", 0);
      }

      mkdir($dir, 0777);

      return ['message' => "Folder created.", 'folder' => $directory . $name];
   }

   public static function delete()
   {
      $request = Builder::request();
      $media = $request->get('media', [], 'ARRAY');
      foreach ($media as $file) {
         if ($file['media_type'] == "folder") {
            self::removeDir(MEDIA_PATH . $file['path']);
         } else {
            \unlink(MEDIA_PATH . $file['path']);
         }
      }
      return true;
   }

   public static function copy()
   {
      $request = Builder::request();
      $media = $request->get('media', [], 'ARRAY');
      foreach ($media as $file) {
         if ($file['media_type'] == "folder") {
            $dir = pathinfo(MEDIA_PATH . $file['path'], PATHINFO_DIRNAME);
            $foldername = pathinfo(MEDIA_PATH . $file['path'], PATHINFO_BASENAME);

            $index = 2;
            $newFoldername = $foldername . '_' . $index;
            while (file_exists($dir . '/' . $newFoldername)) {
               $index++;
               $newFoldername = $foldername . '_' . $index;
            }

            self::copyFolder(MEDIA_PATH . $file['path'], $dir . '/' . $newFoldername);
         } else {
            self::copyFile(MEDIA_PATH . $file['path']);
         }
      }
      return true;
   }

   public static function rename()
   {
      $request = Builder::request();
      $media = $request->get('media', [], 'ARRAY');
      $name = $request->get('name', '', 'RAW');

      if (!empty($media['path'])) {
         $dir = pathinfo(MEDIA_PATH . $media['path'], PATHINFO_DIRNAME);
         $basename = pathinfo(MEDIA_PATH . $media['path'], PATHINFO_BASENAME);
         try {
            rename($dir . '/' . $basename, $dir . '/' . $name);
         } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
         }
      }
      return true;
   }

   public static function copyFile($path)
   {
      $filename = pathinfo($path, PATHINFO_FILENAME);
      $ext = pathinfo($path, PATHINFO_EXTENSION);
      $dir = pathinfo($path, PATHINFO_DIRNAME);

      $index = 2;
      $newFilename = $filename . '_' . $index . '.' . $ext;
      while (file_exists($dir . '/' . $newFilename)) {
         $index++;
         $newFilename = $filename . '_' . $index . '.' . $ext;
      }

      copy($path, $dir . '/' . $newFilename);
   }

   public static function copyFolder($src, $dst)
   {
      $dir = opendir($src);
      @mkdir($dst);
      while (false !== ($file = readdir($dir))) {
         if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
               self::copyFolder($src . '/' . $file, $dst . '/' . $file);
            } else {
               $basename = pathinfo($src . '/' . $file, PATHINFO_BASENAME);
               if (!in_array($basename, self::$unwanted_ext)) {
                  copy($src . '/' . $file, $dst . '/' . $file);
               }
            }
         }
      }
      closedir($dir);
   }

   public static function removeDir($dirPath)
   {
      if (!is_dir($dirPath)) {
         throw new \Exception("$dirPath must be a directory");
      }
      if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
         $dirPath .= '/';
      }
      $files = glob($dirPath . '*', GLOB_MARK);
      foreach ($files as $file) {
         if (is_dir($file)) {
            self::removeDir($file);
         } else {
            unlink($file);
         }
      }
      self::removeUnwantedFiles($dirPath);
      if (self::isDirEmpty($dirPath)) {
         rmdir($dirPath);
      }
   }

   public static function isDirEmpty($dir)
   {
      if (!is_readable($dir)) {
         return false;
      }
      return (count(scandir($dir)) == 2);
   }

   public static function removeUnwantedFiles($dirPath)
   {
      foreach (self::$unwanted_ext as $file) {
         if (file_exists($dirPath . $file)) {
            @unlink($dirPath . $file);
         }
      }
   }

   public static function upload()
   {
      $request = Builder::request();
      $dir = $request->get('dir', '', 'RAW');
      $media = $request->get('media', 'image');

      if (empty($dir)) {
         $dir = MEDIA_PATH . date('Y');
         if (!file_exists($dir)) {
            mkdir($dir, 0777);
         }
         if (!file_exists($dir . '/' . date('m'))) {
            mkdir($dir . '/' . date('m'), 0777);
         }
         if (!file_exists($dir . '/' . date('m') . '/' . date('d'))) {
            mkdir($dir . '/' . date('m') . '/' . date('d'), 0777);
         }

         $uploadDir = $dir . '/' . date('m') . '/' . date('d');
         $dir = date('Y') . '/' . date('m') . '/' . date('d');
      } else {
         $uploadDir = MEDIA_PATH . $dir;
      }

      $fieldName = 'file';
      $return = [];

      $fileCount = count($_FILES[$fieldName]['name']);

      for ($i = 0; $i < $fileCount; $i++) {
         try {
            $fileError = $_FILES[$fieldName]['error'][$i];
            if ($fileError > 0) {
               switch ($fileError) {
                  case 1:
                     throw new \Exception(\JText::_('JDB_ERROR_LARGE_FILE'));
                     return;

                  case 2:
                     throw new \Exception(\JText::_('JDB_ERROR_FILE_HTML_ALLOW'));
                     return;

                  case 3:
                     throw new \Exception(\JText::_('JDB_ERROR_FILE_PARTIAL_ALLOW'));
                     return;

                  case 4:
                     throw new \Exception(\JText::_('JDB_ERROR_NO_FILE'));
                     return;
               }
            }

            $pathinfo = pathinfo($_FILES[$fieldName]['name'][$i]);
            $uploadedFileExtension = $pathinfo['extension'];
            $uploadedFileExtension = strtolower($uploadedFileExtension);

            $validFileExts = [];
            if ($media == 'image') {
               $validFileExts = explode(',', 'jpeg,jpg,png,gif,ico,odg,xcf,bmp,tiff,webp,svg');
            }
            if ($media == 'video') {
               $validFileExts = explode(',', 'mp4,mpeg,mpg');
            }
            if (!in_array($uploadedFileExtension, $validFileExts)) {
               throw new \Exception(\JText::sprintf('JDB_ERROR_INVALID_EXTENSION', implode(', ', $validFileExts)));
            }

            $fileTemp = $_FILES[$fieldName]['tmp_name'][$i];

            if ($media == 'image' && $uploadedFileExtension != 'svg') {
               $imageinfo = getimagesize($fileTemp);
               $okMIMETypes = 'image/jpeg,image/pjpeg,image/png,image/x-png,image/gif,image/x-icon,image/vnd.microsoft.icon';
               $validFileTypes = explode(",", $okMIMETypes);
               if (!is_int($imageinfo[0]) || !is_int($imageinfo[1]) || !in_array($imageinfo['mime'], $validFileTypes)) {
                  throw new \Exception(\JText::_('JDB_ERROR_INVALID_FILETYPE'));
               }
            }

            $ext = $pathinfo['extension'];
            $fileName = preg_replace("/[^A-Za-z0-9]/i", "-", $pathinfo['filename']);
            $index = 2;
            $newFilename = $fileName . '.' . $ext;
            while (file_exists($uploadDir . '/' . $newFilename)) {
               $newFilename = $fileName . '_' . $index . '.' . $ext;
               $index++;
            }
            $uploadPath = $uploadDir . '/' . $newFilename;
            if (!\JFile::upload($fileTemp, $uploadPath)) {
               throw new \Exception(\JText::_('JDB_ERROR_UPLOAD_ERROR'));
            }
            $return[] = ['filename' => $newFilename, 'status' => 'success'];
         } catch (\Exception $e) {
            $return[] = ['filename' => $_FILES[$fieldName]['name'][$i], 'status' => 'error', 'message' => $e->getMessage()];
         }
      }
      $response = [];
      $response['dir'] = $dir;
      $response['files'] = $return;
      return $response;
   }

   public static function getVideoPlayer($url, $config = [], $attrs = [], $class = [])
   {
      $parsedUrl = parse_url($url);
      $type = "";
      switch ($parsedUrl['host']) {
         case 'www.youtube.com':
         case 'youtube.com':
         case 'www.youtu.be':
         case 'youtu.be':
            $type = "youtube";
            $videoId = Helper::getQueryString($url, 'v');
            break;
         case 'vimeo.com':
         case 'www.vimeo.com':
            $type = "vimeo";
            $videoId = substr(parse_url($url, PHP_URL_PATH), 1);
            break;
         default:
            $type = "video";
            break;
      }

      $options = [];
      foreach ($config as $key => $value) {
         $options[] = $key . '=' . $value;
      }
      $options = empty($options) ? '' : '?' . implode('&', $options);

      switch ($type) {
         case "youtube":
            return '<iframe frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen src="https://www.youtube.com/embed/' . $videoId . $options . '"' . (!empty($attrs) ? ' ' . implode(' ', $attrs) : '') . '' . (!empty($class) ? ' class="' . implode(' ', $class) . '"' : '') . '></iframe>';
            break;
         case "vimeo":
            return '<iframe frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen src="https://player.vimeo.com/video/' . $videoId . $options . '"' . (!empty($attrs) ? ' ' . implode(' ', $attrs) : '') . '' . (!empty($class) ? ' class="' . implode(' ', $class) . '"' : '') . '></iframe>';
            break;
         case "video":

            $options = [];
            foreach ($config as $key => $value) {
               if (!empty($value)) {
                  $options[] = $key;
               }
            }
            $options = empty($options) ? '' : ' ' . implode(' ', $options);

            return '<video' . $options . '' . (!empty($attrs) ? ' ' . implode(' ', $attrs) : '') . '' . (!empty($class) ? ' class="' . implode(' ', $class) . '"' : '') . '><source src="' . $url . '" type="video/mp4">Your browser does not support the video tag.</video>';
            break;
      }
   }

   public static function download()
   {
      $request = Builder::request();
      $path = $request->get('path', '', 'RAW');
      $url = $request->get('url', '', 'RAW');
      $name = $request->get('name', '', 'RAW');
      if (empty($url)) {
         throw new \Exception("Downloading URL is invalid.");
      }

      if (!file_exists(MEDIA_PATH . $path)) {
         mkdir(MEDIA_PATH . $path, 0777);
      }
      $dir = MEDIA_PATH . $path . date('Y');
      if (!file_exists($dir)) {
         mkdir($dir, 0777);
      }
      if (!file_exists($dir . '/' . date('m'))) {
         mkdir($dir . '/' . date('m'), 0777);
      }
      if (!file_exists($dir . '/' . date('m') . '/' . date('d'))) {
         mkdir($dir . '/' . date('m') . '/' . date('d'), 0777);
      }

      $uploadDir = $dir . '/' . date('m') . '/' . date('d') . '/';
      $dir = date('Y') . '/' . date('m') . '/' . date('d');

      $pathinfo = pathinfo(parse_url($url)['path']);
      $name = empty($name) ? $pathinfo['filename'] : $name;
      $filename = $name . '.' . $pathinfo['extension'];

      $index = 2;
      while (file_exists($uploadDir . $filename)) {
         $filename = $name . '_' . $index  . '.' . $pathinfo['extension'];
         $index++;
      }

      file_put_contents($uploadDir . $filename, file_get_contents($url));

      return ['file' => $path . $dir . '/' . $filename];
   }
}
