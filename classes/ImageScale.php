<?php

class ImageScale
{

// set quality resp. compression for the whole script
// jpeg_quality = 100; and $png_compression = 0 are highest quality resp. no compression 
private	$jpeg_quality = 100; // set quality of image (IJG 75)
private $png_compression = 0; // 0 - 9

// set two associative arrays for attribute settings
private $source, $destination = array(	"directory" => "", 
										"filename" => "", 
										"filepath" => "",
										"height" => "",
										"width" => "",
										"filetype" => "",
										"extension" => "",
										"imagehandler" => "" );

	public function __construct()
	{
		$itsfuckingnothing = true; // nothing...
	}

	// param-types are string,string,string,int,int
	public function startDispatcher($source_dir, $source_filename, $destination_dir, $destination_height, $destination_width) // dispatcher for all the things
	{
		if (preg_match("../", $source_dir))
		{
			
		}
		// validate params given in startDispatcher()
		if (is_string($source_dir) && is_string($source_filename) && is_string($destination_dir) && is_int($destination_height) && is_int($destination_width))			
		{
			// set source & destination assoc_array values
			$this->setDirectorys($source_dir, $destination_dir);
			$this->setDestinationScale($destination_height, $destination_width);
			$this->setFilenames($source_filename);
			$this->setFilepaths();
			$this->setSourceScale();
			$this->createImagehandler();

			// break scaling if file is already scaled or requested scale ist bigger than original image
			if ($this->isCached())
			{
				return $this->destination["filepath"];
			}
			elseif ($this->isBigger())
			{
				return $this->source["filepath"];
			}

			// scale image and save it to destination folder
			$this->scaleImage();
			$this->saveScaledImage();	

			// return the destination-filepath back to the user
			return $this->destination["filepath"];
		}
		else
		{
			// much error, wow!, so trigger, wow 
			trigger_error("startDispatcher(s,s,s,i,i) : wrong param type.");
		}
	}

	private function setDirectorys($source, $destination)
	{
		if (file_exists($source) && file_exists($destination))
		{
			$this->source["directory"] = $source;
			$this->destination["directory"] = $destination;
			return 1;
		}
		else
		{
			trigger_error("setDirectory(\$source, \$destination) : param: $source, $destination : no source or destination directory");
			return 0;
		}
	}

	private function setDestinationScale($height, $width)
	{
			$this->destination["height"] = $height;
			$this->destination["width"] = $width;
			return 1;
	}

	private function setFilenames($source_filename)
	{
		if (file_exists($this->source["directory"] . $source_filename))
		{
			$this->source["filename"] = $source_filename;
			$this->destination["filename"] = $source_filename . "_" . $this->destination["height"] . "x" . $this->destination["width"];
			return 1;
		}
		else
		{
			trigger_error("setFilename(\$source : param: $source : file don't exists.");
			return 0;
		}
	}

	private function setFilepaths()
	{
		$this->source["filepath"] = $this->source["directory"] . $this->source["filename"];

		$this->setFiletypes();
		$this->setExtensions();

		$this->destination["filepath"] = $this->destination["directory"] . $this->destination["filename"] . "." . $this->destination["extension"];

		return 1;
	}

	private function setFiletypes()
	{
		$this->source["filetype"] = mime_content_type($this->source["filepath"]);
		$this->destination["filetype"] = $this->source["filetype"];

		return 1;
	}

	private function setExtensions()
	{
			if ($this->source["filetype"] == "image/jpeg")
			{
				$this->source["extension"] = "jpeg"; 
				$this->destination["extension"] = "jpeg";
				return 1;
			}
			elseif ($this->source["filetype"] == "image/png")
			{
				$this->source["extension"] = "png"; 
				$this->destination["extension"] = "png";
				return 1;
			}
			elseif ($this->source["filetype"] == "image/gif")
			{
				$this->source["extension"] = "gif"; 
				$this->destination["extension"] = "gif";
				return 1;
			}
			else 
			{
				throw new Exception("getFiletype()->Error: Filetype (" . $filetype. ") not supported.");
				return 0;
			}
	}

	private function setSourceScale()
	{
		//getimagesize gives back array, we take only first two keys of the array
		list($width, $height) = getimagesize($this->source["filepath"]);

		$this->source["height"] = $height;
		$this->source["width"] = $width;

		return 1;
	}

	private function createImagehandler()
	{
		if ($this->source["filetype"] == "image/jpeg")
		{
			$this->source["imagehandler"] = imagecreatefromjpeg($this->source["filepath"]);
			$this->destination["imagehandler"] = imagecreatetruecolor($this->destination["width"], $this->destination["height"]);

			return 1;
		}
		elseif ($this->source["filetype"] == "image/png")
		{
			$this->source["imagehandler"] = imagecreatefrompng($this->source["filepath"]);
			$this->destination["imagehandler"] = imagecreatetruecolor($this->destination["width"], $this->destination["height"]);

			return 1;
		}
		elseif ($this->source["filetype"] == "image/gif")
		{
			$this->source["imagehandler"] = imagecreatefromgif($this->source["filepath"]);
			$this->destination["imagehandler"] = imagecreatetruecolor($this->destination["width"], $this->destination["height"]);

			return 1;
		}
		else
		{
			return 0;
		}		
	}

	private function scaleImage()
	{
		// somebody should comment this line
		$ret = imagecopyresized($this->destination["imagehandler"], $this->source["imagehandler"], 0, 0, 0, 0, $this->destination["width"], $this->destination["height"], $this->source["width"], $this->source["height"]);

		imagedestroy($this->source["imagehandler"]);
		return $ret;
	}

	private function isCached()
	{
		if (file_exists($this->destination["filepath"]))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	private function isBigger()
	{
		if ($this->destination["height"] > $this->source["height"] || $this->destination["width"] > $this->source["width"])
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	private function saveScaledImage()
	{
		if ($this->destination["filetype"] == "image/jpeg")
		{
			$ret = imagejpeg($this->destination["imagehandler"], $this->destination["filepath"], $this->jpeg_quality);
		}
		elseif ($this->destination["filetype"] == "image/png")
		{
			$ret = imagepng($this->destination["imagehandler"], $this->destination["filepath"], $this->png_compression);
		}
		elseif ($this->destination["filetype"] == "image/gif")
		{
			$ret = imagegif($this->destination["imagehandler"], $this->destination["filepath"]);
		}

		imagedestroy($this->destination["imagehandler"]);
		return $ret;
	}

}
?>