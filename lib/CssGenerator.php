<?php
/**
* Generate a CSS sprite with few images
*
* @package  PHP_CSS_Generator
* @version  1.0.0b
* @author   Mickael PERNIN <mickael.pernin@epitech.eu>
* 
*/
class CssGenerator {

/**
* @var string|void $folder	Content the folder name
*/
private $folder;

/**
* @var string $extension	Content the image file extension
*/
private $extension = ".png";

/**
* @var string $extension_css  Content the css file extension
*/
private $extension_css = ".css";

/**
* @var string $name			Content the image file name
*/
private $name = "sprite";

/**
* @var string $css_name		Content the css file name
*/
private $css_name = "style";

/**
* @var int $white_space		Content the padding between each image
*/
private $white_space = 0;

/**
* @var int|false $size		Content the size of each image, false = no resize
*/
private $size = false;

/**
* @var string $output	Content the folder output name
*/
private $output = "output/";

/**
* @var gd|void $empty_img	Content the void image for final output
*/
private $empty_img;

/**
* @var int $rows	Content the rows number
*/
private $rows = 1;

/**
* @var int $cols	Content the cols number
*/
private $cols = 0;

/**
* @var bool $want_subfolder		Content the user choice for recursivity in folder
*/
private $want_subfolder = false;

/**
* @var array[] $flags_tmp		Content all user flags
*/
private $flags_tmp = [];

/**
* @var array[] $files		Content all user files
*/
private $files = [];

/**
* @var array[] $info_img		Content all images files infos
*/
private $info_img = [];

/**
* @var array[] $image_dimension		Content the final image size
*/
private $image_dimension = ["w" => 0, "h" => 0];

/**
* @var const[] SHORTS_OPTIONS		Content full flags names for each short flag
*/
const SHORTS_OPTIONS = [
  "r" => "recursive",
  "i" => "output-image",
  "s" => "output-style",
  "p" => "padding",
  "o" => "override-size",
  "c" => "columns_number"
];

/**
* @var const[] LONGS_OPTIONS		Content what value each flag need
*/
const LONGS_OPTIONS = [
  "recursive" => [null],
  "output-image" => [true, "string"],
  "output-style" => [true, "string"],
  "padding" => [true, "int"],
  "override-size" => [true, "int"],
  "columns_number" => [true, "int"]
];

/**
* @var const[] OPTIONS_LINKS		Content the flags param for the class
*/
const OPTIONS_LINKS = [
  "recursive" => "want_subfolder",
  "output-image" => "name",
  "output-style" => "css_name",
  "padding" => "white_space",
  "override-size" => "size",
  "columns_number" => "cols",
];

/**
* 
* Init the program
*
* @param array $_args  Content all arguments
*
* @return void
* @access public
*/
public function __construct($args) {
  $this->catch_args($args);
  $this->catch_flags();
  $this->check_folder();
  $this->create_image();
}

/**
* 
* Catch all CLI arguments and save the folder name and all flags
*
* @param array $_args  Content all arguments
*
* @throws NO_DIRECTORY
* @return void
* @access public
*/
public function catch_args(array $_args) {
  array_shift($_args);
  if (empty($_args)) {
    throw new Exception("", MSGLogs::NO_DIRECTORY);
  }

  $this->folder = array_pop($_args);
  if (!empty($_args))
    $this->flags_tmp = $_args;
}

/**
* 
* Check if file exists and is a folder then catch all files in subfolers
*
* @throws NO_DIRECTORY, FILE_NO_EXISTS, FILE_NOT_FOLDER, EMPTY_OR_NO_PNG
* @return void
* @access public
*/
public function check_folder() {
  if (empty($this->folder))
    throw new Exception("", MSGLogs::NO_DIRECTORY);

  if (!file_exists($this->folder)) {
    throw new Exception($this->folder, MSGLogs::FILE_NO_EXISTS);
  }

  if (!is_dir($this->folder)) {
    throw new Exception($this->folder, MSGLogs::FILE_NOT_FOLDER);
  }

  $this->iterative($this->folder);
  if (empty($this->files)) {
    throw new Exception($this->folder, MSGLogs::EMPTY_OR_NO_PNG);
  }

  sort($this->files);
}

/**
* 
* Catch flags needed by user, remove "-" on each flag and put them in an array
*
* @throws INVALID_FLAG, FLAG_NOT_EXISTS
* @return void|Exception
* @access public
*/
public function catch_flags() {
  if (empty($this->flags_tmp)) {
    return false;
  }

  $files_numb = count($this->flags_tmp);
  for ($i = 0; $i < $files_numb; $i++) {
    if (preg_match("/^-/", $this->flags_tmp[$i])) {
      $array = $this->find_flag_arg($this->flags_tmp, $i);
      $flag = preg_replace("/^-{1,}/", "", $array[0]);
      $arg = preg_replace("/^-/", "", $array[1]);

      if($this->check_flag_exists($flag)) {
        $this->do_flag($flag, $arg);
      } else {
        throw new Exception($flag, MSGLogs::INVALID_FLAG);
      }
    } else {
      throw new Exception($this->flags_tmp[$i], MSGLogs::FLAG_NOT_EXISTS);
    }
  }
}

/**
* 
* Create final image
*
* @return false
* @access public
*/
public function create_image() {
  $this->load_files();
  $this->create_empty_image();
  foreach ($this->info_img as $image) {
    if (is_string($image["img"])) {
      $tmp = imagecreatefrompng($image["img"]);
    }
    else {
      $tmp = $image["img"];
    }
    $a = $image;
    list($dst_x, $dst_y, $w, $h) = [$a["dst_x"],$a["dst_y"],$a["w"],$a["h"]];
    imagecopy($this->empty_img, $tmp, $dst_x, $dst_y, 0, 0, $w, $h);
    imagedestroy($tmp);
  }
  if (!file_exists("output")) {
    mkdir("output"); 
  }
  $out_folder = preg_replace("/\/{1,}$/", "", $this->output) . "/";
  $this->check_extension_name_image();
  $file = $out_folder . $this->name . $this->extension;
  imagepng($this->empty_img, $file);
  $this->create_css_file($out_folder);

  return false;
}

/**
* 
* Create final css file
*
* @param string $_folder Content folder's output name
*
* @return void
* @access public
*/
public function create_css_file($_folder) {
  $css_lines = "";
  $count = count($this->info_img);
  for ($i = 0; $i < $count; $i++) {
    $css_lines .= "." . $this->info_img[$i]["name"];
    if ($i < $count - 1) {
      $css_lines .= ", ";
    }
  }
  $css_lines .= " { display: inline-block; ";
  $css_lines .= "background: url(" . $this->name . $this->extension . ") ";
  $css_lines .= "no-repeat; }\n\n";
  foreach ($this->info_img as $a) {
    list($dst_x, $dst_y, $w, $h) = [$a["dst_x"],$a["dst_y"],$a["w"],$a["h"]];
    $name = $a["name"];
    $css_lines .= "." . $name . " { ";
    $css_lines .= "background-position: -" . $dst_x . "px -" . $dst_y . "px;";
    $css_lines .= "width: " . $w . "px;";
    $css_lines .= "height: " . $h . "px;";
    $css_lines .= " }\n";
  }
  $this->check_extension_name_css();
  $file = $_folder . $this->css_name . $this->extension_css;
  file_put_contents($file, $css_lines);
}

/**
* 
* Take all png files in a (sub)directory
*
* @param string $_folder Content folder's files name
*
* @throws CANNOT_ACCESS_FOLDER
* @return void|Exception
* @access private
*/
private function iterative($_folder) {
  if (is_readable($_folder)) {
    if ($dir = opendir($_folder)){
      while (($file = readdir($dir)) !== false) {
        $filder = $_folder . "/" . $file;
        if (!preg_match("/^\.(.*)$/", $file)) {
          if (!is_dir($filder)) {

            if (preg_match("/^(.*)$this->extension$/i", $file)) {
              $this->files[] = $filder;
            }
          }
          else {
            if ($this->want_subfolder) {
              $this->iterative($filder);
            }
          }
        }
      }
      closedir($dir);
    }
  } else {
    throw new Exception($_folder, MSGLogs::CANNOT_ACCESS_FOLDER);
  }
}

/**
* 
* Create empty final image
*
* @return void
* @access private
*/
private function create_empty_image() {
  $image = imagecreatetruecolor($this->image_dimension["w"], $this->image_dimension["h"]);
  $background = imagecolorallocatealpha($image, 0, 0, 0, 127); // rgba
  imagefill($image, 0, 0, $background);
  imagealphablending($image, false);
  imagesavealpha($image, true);
  //imagecolortransparent($image, 0);

  $this->empty_img = $image;
}

/**
* 
* Create empty final image
*
* @return void
* @access private
*/
private function load_files() {
  $this->set_cols_and_rows();
  $i = 0;
  for ($y = 0; $y < $this->rows; $y++) {
    for ($x = 0; $x < $this->cols; $x++) {
      $this->load_files_error($i);
      preg_match("/^(.*)\/(.*)$this->extension$/i", $this->files[$i], $name);
      list($w, $h) = getimagesize($this->files[$i]);
      $this->resize_img($this->files[$i]);
      $w = ($this->size) ? $this->size : $w;
      $h = ($this->size) ? $this->size : $h;
      $dst_x = $this->set_destX($i, $x, $y);
      $dst_y = $this->set_destY($i, $y);
      $this->info_img[$i] = [ "img" => $this->files[$i],
                              "w" => $w,
                              "h" => $h,
                              "dst_x" => $dst_x,
                              "dst_y" => $dst_y,
                              "name" => end($name)
                            ];
      if (++$i >= count($this->files)) {
        break;
      }
    }
  }
  $this->setimage_dimension();
}

/**
* 
* Check if files are real image and png using another method
*
* @param int $i Content file key
*
* @throws NOT_IMG, NOT_PNG
* @return void
* @access private
*/
private function load_files_error($i) {
   if (!$this->check_real_image($this->files[$i])) {
      throw new Exception($this->files[$i], MSGLogs::NOT_IMG);
    }
    if (!$this->check_real_image($this->files[$i], true)) {
      throw new Exception($this->files[$i], MSGLogs::NOT_PNG);
    }
}

/**
* 
* Set cols and rows number
*
* @return void|Exception
* @access private
*/
private function set_cols_and_rows() {
  if ($this->cols < 1 || $this->cols > count($this->files)) {
    $this->cols = count($this->files);
  }
  else {
    $this->rows = ceil(count($this->files) / $this->cols);
  }
}

/**
* 
* Set destination X of the image
*
* @param int $i Content the image key
* @param int $x Content the X current col
* @param int $y Content the Y current row
*
* @return int
* @access private
*/
private function set_destX($i, $x, $y) {
  $dst_x = 0;
  if ($x > 0) {
    if ($y == 0) {
      $dst_x = $this->white_space + $this->info_img[$i - 1]["w"];
      $dst_x += $this->info_img[$i - 1]["dst_x"];
    }
    else {
      $a = $i - $this->cols;
      $b = $i - 1;
      if ($this->info_img[$a]["w"] < $this->info_img[$b]["w"]) {
        $dst_x = $this->white_space + $this->info_img[$b]["w"];
        $dst_x += $this->info_img[$b]["dst_x"];
      }
      else {
        $dst_x = $this->info_img[$i - $this->cols]["dst_x"];
      }
    }
  }
  return $dst_x;
}

/**
* 
* Set destination Y of the image
*
* @param int $i Content the image key
* @param int $y Content the Y current row
*
* @return int
* @access private
*/
private function set_destY($i, $y) {
  $dst_y = 0;

  if ($y > 0) {
    $a = $i - $this->cols;
    $dst_y = $this->white_space;
    $dst_y += $this->info_img[$a]["h"] + $this->info_img[$a]["dst_y"];
  }

  return $dst_y;
}

/**
* 
* Check if user's flag exists in short or long option
*
* @param string &$_flag Content the flag
*
* @return bool
* @access private
*/
private function check_flag_exists(&$_flag) {
  if (array_key_exists($_flag, self::SHORTS_OPTIONS)) {
    $_flag = self::SHORTS_OPTIONS[$_flag];
    return true;
  }
  else {
    foreach (self::LONGS_OPTIONS as $option => $v) {
      $tmp = substr($option, 0, strlen($_flag));
      if ($tmp == $_flag) {
        $_flag = $option;
        return true;
      }
    }
    return false;
  }
}

/**
* 
* Run the flag
*
* @param string $_flag Content the flag
* @param string $_arg Content the argument
*
* @throws INVALID_ARGUMENT, EMPTY_ARGUMENT, BAD_ARGUMENT_TYPE
* @return void
* @access private
*/
private function do_flag($_flag, $_arg) {
  if (is_null(self::LONGS_OPTIONS[$_flag][0])) {
    if (!empty($_arg)) {
      throw new Exception($_arg.",".$_flag, MSGLogs::INVALID_ARGUMENT);
    } else {
      $var = self::OPTIONS_LINKS[$_flag];
      if (!$this->$var) {
        $this->$var = true;
      }
    }
  } elseif (self::LONGS_OPTIONS[$_flag][0]) {
    if (empty($_arg)) {
      throw new Exception($_flag, MSGLogs::EMPTY_ARGUMENT);
    } else {
      if ($this->check_type($_flag, $_arg)) {
        $var = self::OPTIONS_LINKS[$_flag];
        $this->$var = $_arg;
      } else {
        throw new Exception($_flag, MSGLogs::BAD_ARGUMENT_TYPE);
      }
    }
  }
}

/**
* 
* Find flag's argument by space or "=" separator
*
* @param string $_flag Content the flag
* @param string &$i Content the flag key
*
* @return array[]
* @access private
*/
private function find_flag_arg($_flag, &$_i) {
  if (preg_match("/=/", $_flag[$_i])) {
    return explode("=", $_flag[$_i]);
  }
  else {
    $array = [$_flag[$_i]];
    if (isset($_flag[$_i + 1])) {
      $arg = $_flag[++$_i];
      if (!preg_match("/^-/", $arg)) {
        $arg = $arg;
      }
      else {
        --$_i;
        $arg = "";
      }
    }
    else {
      $arg = "";
    }

    $array[] = $arg;
    return $array;
  }
}

/**
* 
* Resize image passed in reference
*
* @param string $&_img Content the file's name
*
* @return void
* @access private
*/
private function resize_img(&$_img) {
  if ($this->size) {
    $_size = $this->size;
    $temp_img = imagecreatefrompng($_img);
    list($w, $h) = getimagesize($_img);
    $image = imagecreatetruecolor($_size, $_size);
    $background = imagecolorallocatealpha($image, 0, 0, 0, 127); // rgba
    imagefill($image, 0, 0, $background);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    imagecopyresampled($image, $temp_img, 0, 0, 0, 0, $_size, $_size, $w, $h);

    $_img = $image;
  }
}

/**
* 
* Check if arg is the flag's type needed
*
* @param string $_flag Content the flag
* @param string $_arg Content the arg
*
* @return bool
* @access private
*/
private function check_type($_flag, $_arg) {
  if (preg_match("/^[0-9]+$/", $_arg)) {
    $type = "int";
  } else {
    $type = gettype($_arg);
  }

  if ($type == self::LONGS_OPTIONS[$_flag][1]) {
    return true;
  }

  if (self::LONGS_OPTIONS[$_flag][1] == "string") {
    return true;
  }

  return false;
}

/**
* 
* Set final empty image width and height
*
* @return void
* @access private
*/
private function setimage_dimension() {
  $i = 1;
  $max_width = 0;
  $max_height = 0;
  $current_width = 0;
  $current_height = 0;
  foreach ($this->info_img as $key => $image) {
    $current_width += $image["w"] + $this->white_space;
    if ($i == $this->cols) {
      if ($max_width < $current_width) {
        $max_width = $current_width;
      }
      $current_width = 0;
      $i = 0;
    }
    $i++;
  }
  $i = 1;
  foreach ($this->info_img as $key => $image) {
    $tmp = $image["h"] + $this->white_space;
    if ($current_height < $tmp) {
      $current_height = $tmp;
    }

    if ($i % $this->cols == 0 || $i == count($this->info_img)) {
      $max_height += $current_height;
      $current_height = 0;
    }
    $i++;
  }
  $this->image_dimension["w"] = $max_width;
  $this->image_dimension["h"] = $max_height;
}

/**
* 
* Check if the file is a real image and png
*
* @param string $_img Content the file's name
* @param bool $_extension Content what program need to check
*
* @return bool
* @access private
*/
private function check_real_image($_img, $_extension = false) {
  $mime_type = finfo_open(FILEINFO_MIME_TYPE);
  $info = finfo_file($mime_type, $_img);
  $info = explode("/", $info);
  if (!$_extension) {
    if ($info[0] != "image") {
      return false;
    }
  } else {
    if ($info[1] != "png") {
      return false;
    }
  }
  finfo_close($mime_type);

  return true;
}

/**
* 
* Check if the image's output name has the .png extension
*
* @return void
* @access private
*/
private function check_extension_name_image() {
  if (preg_match("/\.(.*)$/", $this->name)) {
    $this->extension = "";
  }
}

/**
* 
* Check if the css's output name has the .css extension
*
* @return void
* @access private
*/
private function check_extension_name_css() {
  if (preg_match("/\.css$/", $this->css_name)) {
    $this->extension_css = "";
  }
}

}

// Check all cols in a superior row for image size