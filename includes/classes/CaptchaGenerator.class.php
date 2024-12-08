<?php

class CaptchaGenerator 
{
    private $image;
    private $width;
    private $height;
    // * Noise character min. size
    private $characterMinSize = 25;
    // * Noise character max. size
    private $characterMaxSize = 30;
    // * Max. degree of text character rotation
    var $maxRotation = 10;
    var $jpegQuality = 70;
	var $draw_toward_hor_line = false;
    // * Text of captcha
    private $text;
    // Density of noise characters
    private $noiseFactor = 3;
    private $generateNoise = true;
    private $generateGrid = true;
    // * Selected font
    private $font;
    private static $fonts = null;
	
	public function __construct($text) {
        $this->text = $text;
        self::loadFonts();
        $this->getRandomFontFile();
        $this->setSize($this->getTextSize() * 30, 50);
    }

    public function setSize($width, $height) {
        $this->width = $width;
        $this->height = $height;
    }
	
	public function setText($text) {
        $this->text = $text;
	}
	public function setCharMinMaxSize($characterMinSize, $characterMaxSize) {
		$this->characterMinSize = $characterMinSize;
    	$this->characterMaxSize = $characterMaxSize;
	}
	
    public function generate() {
		$this->image = $this->createImage();
        $backgroundColor = $this->createColor($this->image,
        Captcha_RgbColor::createRandomColor(224, 255));
        imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, $backgroundColor);

        $this->fillWithNoiseOrGrid();
        $this->generateText();

		header("Content-type:image/jpeg");
		
        ob_start();
        imagejpeg($this->image, null, $this->jpegQuality);
        $imageText = ob_get_contents();
		ob_end_clean();
        imagedestroy($this->image);
		
        return $imageText;
    }
	
	private static function get_dirfiles($directory, $extension) 
	{ 
		$files = array();
		if ( $handle = opendir($directory) ) { 
			while ( false !== ($file = readdir($handle)) ) 
			{ 
				if ( empty($extension) || is_integer(strpos($file, $extension)) )
					$files[$directory.$file] = $file;
			} 
		} 
		closedir($handle);
		return $files;
	} 

	private static function loadFonts() {
		
        if(self::$fonts !== null) {
            return;
        }
        $directory = dirname(__FILE__) . "/font/";
		
        self::$fonts = array();
		$files = self::get_dirfiles($directory, '.ttf');
		foreach ($files as $fullName => $file) {
            self::$fonts[$fullName] = $fullName;
        }
    }

    private function createImage() {
        if( function_exists('imagecreatetruecolor') ) {
            return imagecreatetruecolor($this->width, $this->height);
        }
        if ( function_exists('imagecreate') ) {
            return imagecreate($this->width, $this->height);
        }
    }

    private function seedRandomGenerator() {
        srand((double) microtime() * 1000000);
    }

    private function getFontFile() {
        return $this->font;
    }

    private function getRandomFontFile() {
        $this->seedRandomGenerator();
        $this->font = array_rand(self::$fonts);
        return $this->font;
    }

    private function getBackgroundNoiseCharacterCount() {
        return $this->noiseFactor * $this->getTextSize();
    }

    private function getTextSize() {
        return strlen($this->text);
    }

    private function generateNoise() {
        for($i=0; $i < $this->getBackgroundNoiseCharacterCount(); $i++) {
            $this->seedRandomGenerator();
            $size = intval(rand((int)($this->characterMinSize / 2.3),
            (int)($this->characterMaxSize / 1.7)));

            $this->seedRandomGenerator();
            $angle = intval(rand(0, 360));

            $this->seedRandomGenerator();
            $left = intval(rand(0, $this->width));

            $this->seedRandomGenerator();
            $top = intval(rand(0, (int)($this->height - ($size / 5))));

            $color = $this->createColor($this->image,
            Captcha_RgbColor::createRandomColor(160, 224));

            $this->seedRandomGenerator();
            $text = chr(intval(rand(45, 250)));

            imagettftext($this->image, $size, $angle, $left, $top, $color,
            $this->getRandomFontFile(), $text);
        }
    }

    private function generateGrid() {
        for($i=0; $i < $this->width; $i += (int)($this->characterMinSize / 1.5)) {
            $color = $this->createColor($this->image,
            Captcha_RgbColor::createRandomColor(160, 224));
            imageline($this->image, $i, 0, $i, $this->height, $color);
        }

        for($i=0 ; $i < $this->height; $i += (int)($this->characterMinSize / 1.8)) {
            $color = $this->createColor($this->image,
            Captcha_RgbColor::createRandomColor(160, 224));
            imageline($this->image, 0, $i, $this->width, $i, $color);
        }
    }

    private function fillWithNoiseOrGrid() {
        if($this->generateNoise) {
            $this->generateNoise();
        }

        if($this->generateGrid) {
            $this->generateGrid();
        }
    }

    private function createColor($image, Captcha_RgbColor $color) {
        return  imagecolorallocate($image, $color->r, $color->g, $color->b);
    }

    private function generateText() 
	{
		$text_color = imagecolorallocate($this->image, 233, 14, 91);
        $left = 0;
        for( $i=0, $left = intval(rand($this->characterMinSize, $this->characterMaxSize));
        	$i < $this->getTextSize(); $i++) 
		{
            $text = strtoupper(substr($this->text, $i, 1));

            $this->seedRandomGenerator();
            $angle = intval(rand(($this->maxRotation * -1), $this->maxRotation));

            $this->seedRandomGenerator();
            $size = intval(rand($this->characterMinSize, $this->characterMaxSize));

            $this->seedRandomGenerator();
			if ( $this->draw_toward_hor_line )
            	$top = round($this->height / 2 + $this->characterMaxSize * 0.5 );
			else
				$top = intval(rand((int)($size * 1.5), (int)($this->height - ($size / 7))));

            $color = $this->createColor($this->image,
            Captcha_RgbColor::createRandomColor(0, 127));

            $shadowRgb = Captcha_RgbColor::createRandomColor(0, 127);
            $shadowRgb->add(127, 127, 127);
            $shadow = $this->createColor($this->image, $shadowRgb);
			if ( $this->draw_toward_hor_line ) {
				ImageTTFText($this->image, $size, $angle, $left, $top,
	            	$color, $this->getFontFile(), $text
				);
			}
			else {
	            ImageTTFText($this->image, $size, $angle, $left + (int)($size / 15), $top,
	            	$shadow, $this->getRandomFontFile(), $text
				);
	            ImageTTFText($this->image, $size, $angle, $left, $top - (int)($size / 15),
	            	$color, $this->getFontFile(), $text
				);
			}
            $left += (int)($size + ($this->characterMinSize / 5));
			
        }
    }
	public function generateRandText($length, $start_char = 97, $end_char = 122) {
        //return strtolower(substr(md5(uniqid()), 0, $length));
		$res = '';
		for ($i = 1; $i <= $length; $i++)
			$res = $res.chr(rand($start_char, $end_char));
		return $res;
    }

}

class Captcha_RgbColor 
{
    public $r;
    public $g;
    public $b;

    public function __construct($r, $g, $b) {
        $this->r = $r;
        $this->g = $g;
        $this->b = $b;
    }
    
    public function add($r, $g, $b) {
        $this->r += $r;
        $this->g += $g;
        $this->b += $b;
    }
    
    // * @return Captcha_RgbColor
    public static function createRandomColor($min, $max) {
        srand((double)microtime() * 1000000);
        $r = intval(rand($min, $max));
        srand((double)microtime() * 1000000);
        $g = intval(rand($min, $max));
        srand((double)microtime() * 1000000);
        $b = intval(rand($min, $max));
        return new Captcha_RgbColor($r, $g, $b);
    }
}

