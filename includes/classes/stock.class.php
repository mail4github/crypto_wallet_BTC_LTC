<?php
require_once(DIR_WS_CLASSES.'user.class.php');
require_once(DIR_WS_CLASSES.'share.class.php');
require_once(DIR_WS_CLASSES.'transaction.class.php');
include_once(DIR_COMMON_PHP.'performance_time.php');
require_once(DIR_WS_CLASSES.'circlecrop.class.php');

class Stock
{
	var $stockid = '';
	var $variables = array();
	var $description_image_number = 0;
	var $description_image_number1 = 0;
	var $description_image_number2 = 0;

	function read_data($stockid = '')
	{
		if ( !empty($stockid) )
			$this->stockid = $stockid;
		if ( !empty($this->stockid) ) {
			if ( is_file_variable_expired('stock_data_'.$this->stockid, 1) /*|| defined('DEBUG_MODE')*/ ) {
				$data = get_api_value('stock_read_data', 'stockid='.urlencode($this->stockid));
				if ( $data && $data['variables'] ) {
					update_file_variable('stock_data_'.$this->stockid, json_encode($data['variables']));
					$this->variables = $data['variables'];
					foreach( $this->variables as $akey => $aval ) 
						$this->$akey = $aval;
					return true;	
				}
			}
			$data = get_file_variable('stock_data_'.$this->stockid);
			if ( $data ) {
				$this->variables = json_decode($data, true);
				foreach( $this->variables as $akey => $aval ) 
					$this->$akey = $aval;
				return true;	
			}
		}
		return false;
	}

	function read_data_from_array($array)
	{
		if ( !empty($array) ) {
			$this->variables = $array;
			foreach( $this->variables as $akey => $aval ) 
				$this->$akey = $aval;
			return true;	
		}
		return false;
	}

	function init_owner($userid = '')
	{
		if ( !$this->owner ) {
			if ( !empty($userid) )
				$this->userid = $userid;
			$this->owner = new User();
			$this->owner->userid = $this->userid;
			$this->owner->read_data(false);
		}
		return $this->owner;
	}
	
	function delete()
	{
		if ( empty($this->stockid) )
			return 'Error: cannot delete Stock.';
		if ( !$this->disabled )
			return 'Error: cannot delete Stock. Stock should be disabled first';
		$data = make_api_request('stock_delete', '', array('stockid' => $this->stockid));
		if ( $data['success'] )
			return $data['values']['error_message'];
		else
			return $data['message'];
	}

	function update(
		$stockid = '',
		$userid = 0,
		$user_websiteid = 0,
		$name = '',
		$url = '',
		$price = 0,
		$total_shares = 0,
		$dividend = 0,
		$dividend_frequency = 0,
		$disabled = null,
		$check_errors = true,

		$tagline = '',
		$cover_image = '',
		$user_image = '',
		$user_name = '',
		$user_city = '',
		$user_country_cc = '',
		$user_reputation = '',
		$user_credit_rating = '',
		$user_paypal = '',
		$description = '',
		$paragraph0_header = null,
		$paragraph1_header = null,
		$description1 = null,
		$paragraph2_header = null,
		$description2 = null,
		$business_plan_xml = null
		)
	{
		if ( !empty($user_image) )
			$user_image = convert_image_to_data_url($user_image, false);
		
		if ( !empty($cover_image) )
			$cover_image = convert_image_to_data_url($cover_image, false);
		if ( empty($this->stockid) )
			$this->stockid = $stockid;
		$data = make_api_request('stock_update', '', array(
			'stockid' => $this->stockid,
			'stock_userid' => $userid,
			'name' => $name,
			'url' => $url,
			'price' => $price,
			'total_shares' => $total_shares,
			'dividend' => $dividend,
			'dividend_frequency' => $dividend_frequency,
			'disabled' => $disabled,
			'check_errors' => $check_errors,
			'tagline' => $tagline,
			'cover_image' => $cover_image,
			'user_image' => $user_image,
			'user_name' => $user_name,
			'user_city' => $user_city,
			'user_country_cc' => $user_country_cc,
			'user_reputation' => $user_reputation,
			'user_credit_rating' => $user_credit_rating,
			'user_paypal' => $user_paypal,
			'description' => $this->set_description($description),
			'paragraph0_header' => $paragraph0_header,
			'paragraph1_header' => $paragraph1_header,
			'description1' => $this->set_description($description1),
			'paragraph2_header' => $paragraph2_header,
			'description2' => $this->set_description($description2),
			'business_plan_xml' => $business_plan_xml));
		
		if ( $data['success'] )
			return $data['values']['error_message'];
		else
			return $data['message'];
	}
	
	function publish($published = 1)
	{
		if ( empty($this->user_image) ) 
			return 'Error: Please upload user image.';
		$data = make_api_request('stock_publish', '', 
			array(
			'stockid' => $this->stockid,
			'published' => $published,
			)
		);
		if ( $data['success'] )
			return $data['values']['error_message'];
		else
			return $data['message'];
	}

	function create_trend_image($fileName, $interval = 365, $width = 320, $height = 110)
	{
		if ( file_exists($fileName) && time() - filemtime($fileName) < 60 * 1 )
			return '';
		$message = '';
		require_once(DIR_WS_INCLUDES.'jpgraph/src/jpgraph.php');
		require_once(DIR_WS_INCLUDES.'jpgraph/src/jpgraph_line.php');
		try {
			global $months;
			$data = get_api_value('stock_get_trend_array', array('stockid' => $this->stockid, 'interval_days' => $interval));
			$ydata = $data['ydata'];
			$months = $data['months'];
			
			// Create the graph and set a scale.
			// These two calls are always required
			$graph = new Graph($width, $height);
			$graph->SetScale('intlin');
			$color = COLOR_TEXT_BKG;
			if ( !is_integer(strpos($color, '#')) )
				$color = '#ffffff';
			$graph->SetBackgroundGradient($color, $color);
			$graph->SetFrame(false);
			
			// Create the linear plot
			$lineplot = new LinePlot($ydata);

			// Add the plot to the graph
			$graph->Add($lineplot);

			$lineplot->SetColor("#0066DD");
			$lineplot->SetStyle("solid");
			$lineplot->SetFillColor("#EBF5FD@0.2");

			$graph->ygrid->Show(false);

			$graph->xaxis->SetLabelFormatCallback('day_callback');
			$graph->yaxis->SetLabelFormatCallback('price_callback');
			$graph->xaxis->SetColor($color);
			$graph->yaxis->SetColor($color);
			
			$tmp_fileName = $fileName.'.tmp';
			unlink($tmp_fileName);

			// write to file
			$graph->Stroke($tmp_fileName);
			if ( file_exists($tmp_fileName) && filesize($tmp_fileName) > 0 ) {
				$message = 'done';
				rename($tmp_fileName, $fileName);
			}
			else {
				$message = 'Error: cannot create image: '.$tmp_fileName.'<br>';
			}
		}
		catch (Exception $e) {
			$message = 'Exception: '.$e->getMessage()."\n<br>";
		}
		return $message;
	}
	
	function update_stats()
	{
		get_api_value('stock_update_stats', 'stockid='.urlencode($this->stockid));
		return true;
	}
	
	function get_list($sort_by = 1, $condition = 1, $limit = '', $credit_rating = 2)
	{
		$stocks_arrays = get_api_value('stock_get_list', '', array('sort' => $sort_by, 'condition' => $condition, 'limit' => $limit, 'objects' => 1, 'credit_rating' => $credit_rating));
		if ( $stocks_arrays ) {
			$stocks_objects = array();
			foreach ( $stocks_arrays as $stocks_array ) {
				$stock = new Stock();
				if ( $stock->read_data_from_array($stocks_array) )
					$stocks_objects[] = $stock;
			}
			return $stocks_objects;
		}
		return false;
	}
	
	function get_cover_image_url($edit_stage = false, $refresh_image = false)
	{
		if ($edit_stage) {
			$s = get_text_between_tags($this->cover_image, ';base64,', '');
			if ( !empty($s) )
				return $this->cover_image;
			else
				return '';
		}
		else {
			if ( empty($this->cover_image) )
				return '/stock/'.$this->stockid.'.jpeg';
				
			else
				return convert_data_url_to_image($this->cover_image, DIR_WS_TEMP_ON_WEBSITE, '/'.DIR_WS_TEMP_NAME, $this->stockid.'_cover_img', $refresh_image);
		}
	}
	
	function get_user_image_url($edit_stage = false)
	{
		if ($edit_stage)
			return $this->user_image;
		else {
			if ( empty($this->user_image) )
				return '/'.DIR_WS_WEBSITE_IMAGES_DIR.'no_photo_60x60boy.png';
			else {
				if ( !is_integer(strpos($this->user_image, 'data:image/')) ) {
					$this->set_user_image($this->user_image);
				}
				return convert_data_url_to_image($this->user_image, DIR_WS_TEMP_ON_WEBSITE, '/'.DIR_WS_TEMP_NAME, $this->stockid.'_user_image', $edit_stage);
			}
		}
	}
	
	function get_user_reputation()
	{
		if (!empty($this->user_reputation))
			return $this->user_reputation;
		else
			return '<b style="color:#800000;">unknown</b>';
	}
	
	function get_user_credit_rating($credit_rating = '')
	{
		$rating_array = array('<font color="#ffc6c6">D-</font>', '<font color="#80ff80">A+</font>', '<font color="#c6e2ff">A-</font>', '<font color="#c6e2ff">B+</font>', '<font color="#d5d5ff">B-</font>', '<font color="#ffc6ff">C+</font>', '<font color="#ffc6c6">C-</font>');
		if ( empty($this->user_credit_rating) )
			$this->user_credit_rating = 4;
		if ( $credit_rating == '' )
			$credit_rating = $this->user_credit_rating;
		return $rating_array[$credit_rating];
	}
	
	function get_user_paypal()
	{
		return $this->user_paypal?'<span style="color:#008000; font-weight:bold;">Verified</span>':'<span style="color:#800000; font-weight:bold;">Not Verified</span>';
	}
	
	function get_description($force_create_files = false, $description_number = '')
	{
		$description_name = 'description'.$description_number;
		$description_image_number = 'description_image_number'.$description_number;
		$url_found = true;
		$search_pos = 0;
		$delimeterLeft = 'src="';
		$delimeterRight = '"';
		$description = $this->$description_name;
		while ( $url_found ) {
			$url_found = false;
			$posLeft = strpos($description, '<img ', $search_pos); 
			if ( $posLeft === false )
				break; 
			$posLeft = strpos($description, $delimeterLeft, $posLeft) + strlen($delimeterLeft); 
			$posRight = strpos($description, $delimeterRight, $posLeft); 
			if ( $posRight === false )
				break;
			$search_pos = $posLeft;
			$url_found = true;
			$url = substr($description, $posLeft, $posRight - $posLeft);
			if ( is_integer( strpos($url, 'data:image/') ) ) {
				$this->$description_image_number++;
				$description = substr_replace($description, convert_data_url_to_image($url, DIR_WS_TEMP_ON_WEBSITE, '/'.DIR_WS_TEMP_NAME, $this->stockid.'_desc_img_'.$description_number.'_'.$this->$description_image_number, $force_create_files), $posLeft, $posRight - $posLeft);
			}
		}
		return $description;
	}

	function stock_is_publick()
	{
		return !empty($this->tagline);
	}
	
	function get_url($for_userid = '')
	{
		return SITE_DOMAIN.'stocks/'.$this->stockid.(!empty($for_userid)?'/r'.tep_sanitize_string($for_userid, 10).'_':'');
	}
	
	function get_image_banner_url()
	{
		if ( !empty($this->stockid) )
			return SITE_DOMAIN.'stock/'.$this->stockid.'.jpeg';
		else
			return '/'.DIR_WS_WEBSITE_IMAGES_DIR.'stocks100x100.png';
	}
	
	function get_image_banner_code($safe_code = 0)
	{
		$s = '<img src="'.$this->get_image_banner_url().'" border="0" title="'.$this->name.'" width="200" height="335">';
		if ( $safe_code )
			$s = str_replace('<', '&lt;', $s);
		return $s;
	}
	
	function get_status()
	{
		return ($this->banned?'banned':($this->published?'published':($this->disabled?'disabled':'active')));
	}
	
	function set_user_image($image_file)
	{
		make_api_request( 'stock_set_user_image', 'stockid='.urlencode($this->stockid), array( 'user_image' => convert_image_to_data_url($image_file, false) ) );
	}
	
	function generate_shares_grid(
		$number_of_columns = 4,
		$number_of_rows = 2,
		$item_class = '',
		$shares_grid_sort = 'top_shares',
		$condition = 'top_shares',
		$credit_rating = 2
	)
	{
		if ( !isset($number_of_columns) || $number_of_columns == '' )
			$number_of_columns = 4;
		if ( !isset($number_of_rows) || $number_of_rows == '' )
			$number_of_rows = 2;
		if ( !isset($item_class) || $item_class == '' )
			$item_class = '';
		if ( !isset($shares_grid_sort) || $shares_grid_sort == '' )
			$shares_grid_sort = 'top_shares';
		if ( !isset($credit_rating) || $credit_rating == '' )
			$credit_rating = 2;
		if ( !isset($condition) || $condition == '' )
			$condition = 'top_shares';
		$shares_grid = "";
		$grid_columns = round(12 / $number_of_columns);
		$stocks = $this->get_list($shares_grid_sort, $condition, $number_of_columns * $number_of_rows, $credit_rating);
		if ( !$stocks )
			return false;
		$col_count = 0;
		$shares_grid = $shares_grid.'
		<style type="text/css" media="all">
			.shares_grid_h1{line-height:normal;}
			.shares_grid_h2{line-height:normal;}
			.shares_grid_h3{line-height:normal;}
			.shares_grid_h4{line-height:normal;}
			.shares_grid_price{/*font-size:'.H1_FONT_SIZE.'px; text-align:center;color:#'.H1_COLOR.'; font-family:"'.H1_FONT_FAMILY.'"; font-weight:'.H1_FONT_WEIGHT.'; font-style:'.H1_FONT_STYLE.'; text-shadow:'.((int)H1_HAS_SHADOW == 1?H1_SHADOW.'px '.H1_SHADOW.'px 1px #'.H1_SHADOW_COLOR:'none').';*/}
			.shares_grid_bottom_spacer{min-height:60px;}
		</style>
		<div class="row shares_grid_row">
		';
		foreach ( $stocks as $grid_stock ) {
			$shares_grid = $shares_grid.'
			<div class="col-sm-'.$grid_columns.' '.$item_class.'" style="position:relative; cursor:pointer;" onclick="location.assign(\'/stocks/'.$grid_stock->stockid.'\');">
				<img src="'.$grid_stock->get_cover_image_url().'" class="img-responsive shares_grid_top_image" alt="'.$grid_stock->name.'" style="display:inline-block;">
				<h1 class="shares_grid_h1">'.shorter_text($grid_stock->name, 20).'</h1>
				<h4 class="shares_grid_h4">'.shorter_text($grid_stock->tagline, 80).'</h4>
				<table class="table table-borderless">
				<tr>
					<td><img class="user_image_on_share" src="'.$grid_stock->get_user_image_url($grid_stock->get_status() == 'published').'" border="0" alt="'.$grid_stock->user_name.'" ></td>
					<td width="100%">
						<h2 class="shares_grid_h2">by <b>'.shorter_text($grid_stock->user_name, 10).'</b></h2>
						<h3 class="shares_grid_h3">
							<span style="padding-right:10px;">'.$grid_stock->user_city.'</span> '.($grid_stock->user_country_cc != ''?'<img src="/'.DIR_WS_IMAGES_DIR.'flags/'.$grid_stock->user_country_cc.'.jpeg" border="0" title="From '.getCountryName($grid_stock->user_country_cc).'" width="24" height="12">':'').'
						</h3>
					</td>
				</tr>
				</table>
				<div class="shares_grid_bottom_spacer"></div>
				<form method="post" action="/acc_checkout.php" style="width:100%; text-align:center; position:absolute; bottom:0px; left:0;">
					<input type="hidden" name="stockid" value="'.$grid_stock->stockid.'">
					<h2 class="shares_grid_ROI">ROI: '.round($grid_stock->get_ROI() * 100).'%</h2>
					<p class="shares_grid_share_price">'.currency_format($grid_stock->stat_current_price).' per '.WORD_MEANING_SHARE.'</p>
					<button class="btn btn-success" style="width:80%;" onclick="$(this).html(\'<img src=/images/wait64x64.gif width=16 height=16 border=0>\');">INVEST NOW</button>
				</form>
			</div>
			';
			$col_count++;
			if ($col_count >= $number_of_columns ) {
				$shares_grid = $shares_grid.'</div><div class="row shares_grid_row">';
				$col_count = 0;
			}
		}
		$shares_grid = $shares_grid.'</div>';
		$shares_grid = $shares_grid.'
		<script type="text/javascript">
		if ( $(window).width() > 750 && navigator.userAgent.indexOf("Opera") < 0 ) {
			$(".shares_grid_row").addClass("row-eq-height");
		}
		</script>
		';
		return $shares_grid;
	}
	
	function user_has_number_of_shares($userid = '')
	{
		if ( empty($userid) )
			return 0;
		return 0;
		//return get_api_value('stock_get_total_amount_shares_sold', array('stockid' => $this->stockid, 'userid' => $userid) );
	}
	
	function get_total_amount_shares_sold()
	{
		//return get_api_value('stock_get_total_amount_shares_sold', 'stockid='.urlencode($this->stockid));
		//return $this->total_amount_shares_sold;
		return $this->total_shares * $this->price * 0.5;
	}
	
	function get_ROI()
	{
		return ($this->dividend / $this->dividend_frequency * 30 * 12 + $this->stat_current_price) / $this->stat_current_price;
	}
	
	function get_number_of_for_sale($price = 0)
	{
		return get_api_value('stock_get_number_of_for_sale', 'stockid='.urlencode($this->stockid), array('price' => $price));
	}
	
	function adjust_quantity()
	{
		if ( $this->get_ROI() > 3 && (float)$this->stat_current_price == SHARE_PRICE_MINIMUM * (1 + PART_OF_TRANSACTION_GOES_TO_BROCKER) ) {
			return $this->get_number_of_for_sale(SHARE_PRICE_MINIMUM);
		}
		else
			return $this->stat_shares_for_sale;
	}
	
	function draw_string($arr_strings, $y, $out_image, $center = true, $left_ingent = 2)
	{
		$str_width = 0;
		$numb_of_symbols = 0;
		$gap_y = 2;
		foreach ($arr_strings as $str) {
			$box = imagettfbbox($str['size'], 0, $str['font-family'], $str['text']);
			$str_width = $str_width + ($box[4] - $box[0]);
			$numb_of_symbols = $numb_of_symbols + strlen($str['text']); 
		}
		if ($center)
			$x = imagesx($out_image) / 2 - $str_width / 2;
		else
			$x = $left_ingent;
		
		$max_simb_in_line = 0;
		if ( $str_width > imagesx($out_image) ) {
			$max_simb_in_line = floor(imagesx($out_image) / ($str_width / $numb_of_symbols));
		}
		foreach ($arr_strings as $str) {
			if ( $max_simb_in_line > 0 ) {
				$new_t = explode("\r\n", ChunkText($str['text'], $max_simb_in_line - 2, "\r\n"));
				foreach ($new_t as $sp_str) {
					$box = imagettfbbox($str['size'], 0, $str['font-family'], $sp_str);
					if ($center)
						$x = imagesx($out_image) / 2 - ($box[4] - $box[0]) / 2;
					else
						$x = $left_ingent;
					imagettftext($out_image, $str['size'], 0, $x, $y, $str['font-color'], $str['font-family'], $sp_str);
					$y = $y + $box[1] - $box[5] + $gap_y;
					$last_height = 0;
				}
				
			}
			else {
				imagettftext($out_image, $str['size'], 0, $x, $y, $str['font-color'], $str['font-family'], $str['text']);
				$box = imagettfbbox($str['size'], 0, $str['font-family'], $str['text']);
				$x = $x + ($box[4] - $box[0]);
				$last_height = $box[1] - $box[5] + $gap_y;
			}
		}
		return $y + $last_height;
	}

	function create_in_image($in_file_name, $in_file_extension)
	{
		if ( $in_file_extension == 'jpg' || $in_file_extension == 'jpeg' ) {
			if ( ! $photo_img = imagecreatefromjpeg($in_file_name) )
				return 'Error: cannot open file. Unknown file format.';
		}
		else
		if ( $in_file_extension == 'png' ) {
			if ( ! $photo_img = imagecreatefrompng($in_file_name) )
				return 'Error: cannot open file. Unknown file format.';
		}
		else
		if ( $in_file_extension == 'gif' ) {
			if ( ! $photo_img = imagecreatefromgif($in_file_name) )
				return 'Error: cannot open file. Unknown file format.';
		}
		return $photo_img;
	}

	function draw_cover_image($in_file_name, $in_file_extension, $out_image)
	{
		if ( empty($in_file_name) )
			return false;

		if ( function_exists('imageantialias') )
			imageantialias($out_image, true);
		$photo_img = $this->create_in_image($in_file_name, $in_file_extension);
		
		$srcW = imagesx($photo_img);
		$srcH = imagesy($photo_img);
		$srcY = 0;
		$srcX = 0;
		$dst_w = imagesx($out_image) - 10;
		$dst_h = $dst_w / imagesx($photo_img) * imagesy($photo_img) - 10;
		
		if ( $srcX < 0 )
			$srcX = 0;
		if ( $srcY < 0 )
			$srcY = 0; 
		if ( $srcW > imagesx($photo_img) )
			$srcW = imagesx($photo_img);
		if ( $srcH > imagesy($photo_img) )
			$srcH = imagesy($photo_img);
		imagecopyresampled(
			$out_image, $photo_img, 
			5, 5, 
			$srcX, $srcY, 
			$dst_w, $dst_h, 
			$srcW, $srcH
		);
		imagedestroy($photo_img);
		return '';
	}

	function draw_image_new($out_file_name, $background_file_name, $website, $price, $price_growth, $price_growth_percent, $dividend, $for_sale, $cover_file_name, $user_face_file_name, $share_name, $tagline, $user_name, $user_city, $user_country_cc)
	{
		
		if ( !$out_image = imagecreatefrompng($background_file_name) )
			return 'Error: cannot open backgrounf file.';
		
		if ( function_exists('imageantialias') )
			imageantialias($out_image, true);
		
		imageAlphaBlending($out_image, true);
		imageSaveAlpha($out_image, true);
		
		$this->draw_cover_image($cover_file_name, 'jpg', $out_image);
		
		$textcolor = imagecolorallocate($out_image, 0, 0, 0);

		$last_y = $this->draw_string(array(
			array('text'=>$share_name, 'size'=>28, 'font-family'=>DIR_WS_INCLUDES.'font/champagne_limousines_bold.ttf', 'font-color'=>$textcolor),
		), 250, $out_image);
		$this->draw_string(array(
			array('text'=>$tagline, 'size'=>18, 'font-family'=>DIR_WS_INCLUDES.'font/champagne_limousines.ttf', 'font-color'=>$textcolor),
		), $last_y, $out_image);
		
		$cover = imagecreatefromjpeg($user_face_file_name);
		$crop = new CircleCrop($cover, 120, 120);
		$cropped_image = $crop->crop();
		imagecopy($out_image, $cropped_image, 12, 400, 0, 0, imagesx($cropped_image), imagesy($cropped_image));
		
		$last_y = $this->draw_string(array(
			array('text'=>'by ', 'size'=>20, 'font-family'=>DIR_WS_INCLUDES.'font/champagne_limousines.ttf', 'font-color'=>$textcolor),
			array('text'=>$user_name, 'size'=>20, 'font-family'=>DIR_WS_INCLUDES.'font/champagne_limousines_bold.ttf', 'font-color'=>$textcolor),
		), 440, $out_image, false, 180);

		$flag_img = $this->create_in_image(DIR_WS_IMAGES.'flags/'.$user_country_cc.'.jpeg', 'jpeg');
		imagecopyresampled(
			$out_image, $flag_img, 
			180, $last_y - 18, 
			0, 0, 
			30, 20, 
			imagesx($flag_img), imagesy($flag_img)
		);

		$last_y = $this->draw_string(array(
			array('text'=>$user_city, 'size'=>20, 'font-family'=>DIR_WS_INCLUDES.'font/champagne_limousines_bold.ttf', 'font-color'=>$textcolor),
		), $last_y, $out_image, false, 220);
		
		$this->draw_string(array(
			array('text'=>$price, 'size'=>40, 'font-family'=>DIR_WS_INCLUDES.'font/champagne_limousines_bold.ttf', 'font-color'=>$textcolor),
			array('text'=>' per '.WORD_MEANING_SHARE, 'size'=>24, 'font-family'=>DIR_WS_INCLUDES.'font/champagne_limousines.ttf', 'font-color'=>$textcolor),
		), 568, $out_image);
		
		if ( !imagepng($out_image, $out_file_name, 9) )
			return 'Error: cannot write to PNG file.';
		imagedestroy($out_image);
		return '';
	}

	function draw_image($out_file_name, $background_file_name, $website, $price, $price_growth, $price_growth_percent, $dividend, $for_sale)
	{
		if ( !$out_image = imagecreatefrompng($background_file_name) )
			return 'Error: cannot open backgrounf file.';
		if ( function_exists('imageantialias') )
			imageantialias($out_image, true);
		imageAlphaBlending($out_image, true);
		imageSaveAlpha($out_image, true);
		
		$textcolor = imagecolorallocate($out_image, 0, 0, 0);
		$bbox = imagettfbbox ( 10, 0, DIR_WS_INCLUDES.'font/Chancery.ttf', $website);
		$x = (imagesx($out_image) / 2) - (($bbox[4] - $bbox[0]) / 2);
		imagettftext($out_image, 10, 0, $x, 72, $textcolor, DIR_WS_INCLUDES.'font/Chancery.ttf', $website);
		
		$s_p = 'Price: '.$price;
		$bbox = imagettfbbox(13, 0, DIR_WS_INCLUDES.'font/Chancery.ttf', $s_p);
		$s_g = '  '.$price_growth_percent;
		$bbox2 = imagettfbbox(11, 0, DIR_WS_INCLUDES.'font/Chancery.ttf', $s_g);
		
		$x = (imagesx($out_image) / 2) - (($bbox[4] - $bbox[0] + $bbox2[4] - $bbox2[0]) / 2);
		imagettftext($out_image, 13, 0, $x, 92, $textcolor, DIR_WS_INCLUDES.'font/Chancery.ttf', $s_p);
		
		$x2 = $bbox[4] - $bbox[0] + (imagesx($out_image) / 2) - (($bbox[4] - $bbox[0] + $bbox2[4] - $bbox2[0]) / 2);
		$textcolor_green = imagecolorallocate($out_image, 0, 128, 0);
		imagettftext($out_image, 11, 0, $x2, 92, $textcolor_green, DIR_WS_INCLUDES.'font/Chancery.ttf', $s_g);

		$s = 'Dividend: '.$dividend;
		$bbox = imagettfbbox ( 13, 0, DIR_WS_INCLUDES.'font/Chancery.ttf', $s);
		$x = (imagesx($out_image) / 2) - (($bbox[4] - $bbox[0]) / 2);
		imagettftext($out_image, 13, 0, $x, 114, $textcolor, DIR_WS_INCLUDES.'font/Chancery.ttf', $s);
		if ( !empty($for_sale) ) {
			$s = 'For Sale: '.$for_sale;
			$bbox = imagettfbbox ( 13, 0, DIR_WS_INCLUDES.'font/Chancery.ttf', $s);
			$x = (imagesx($out_image) / 2) - (($bbox[4] - $bbox[0]) / 2);
			imagettftext($out_image, 13, 0, $x, 136, $textcolor, DIR_WS_INCLUDES.'font/Chancery.ttf', $s);
		}
		
		if ( !imagepng($out_image, $out_file_name, 9) )
			return 'Error: cannot write to PNG file.';
		imagedestroy($out_image);
		return '';
	}

	function get_share_image()
	{
		$image_url = 'share_200x335_'.$this->stockid.'.png';
		$image_file = DIR_WS_TEMP_ON_WEBSITE.$image_url;
		if ( time() - filemtime($image_file) > 60 * 60 || !file_exists($image_file) /*|| defined('DEBUG_MODE')*/ ) {
			$growth = $this->stat_current_price - $this->stat_price_30_day_ago;
			if ( $growth > 0  ) {
				$growth = currency_format($growth);
				if ( $this->stat_price_30_day_ago > 0 ) 
					$growth_percent = '+'.number_format($growth / $this->stat_price_30_day_ago * 100).'%';
				else
					$growth_percent = '+100%';
			}
			else {
				$growth = '';
				$growth_percent = '';
			}
			if ( !empty($this->tagline) ) {
				$s = $this->draw_image_new($image_file, DIR_WS_IMAGES.'share_bkg_new.png', get_domain($this->url), currency_format($this->stat_current_price), $growth, $growth_percent, currency_format($this->dividend / $this->dividend_frequency * 30), $this->stat_shares_for_sale > 0?$this->stat_shares_for_sale:'', empty($this->cover_image)?'':DIR_ROOT.WEBSITE_FRONT_DIR.$this->get_cover_image_url(), DIR_ROOT.WEBSITE_FRONT_DIR.$this->get_user_image_url(), convert_html2text($this->name), convert_html2text($this->tagline), $this->user_name, $this->user_city, $this->user_country_cc);
			}
			else
				$s = $this->draw_image($image_file, DIR_WS_IMAGES.'share_bkg.png', get_domain($this->url), currency_format($this->stat_current_price), $growth, $growth_percent, currency_format($this->dividend / $this->dividend_frequency * 30), $this->stat_shares_for_sale > 0?$this->stat_shares_for_sale:'');
		}
		return '/'.DIR_WS_TEMP_NAME.$image_url;
	}

	function set_description($description)
	{
		$url_found = true;
		$search_pos = 0;
		$delimeterLeft = 'src="';
		$delimeterRight = '"';
		while ( $url_found ) {
			$url_found = false;
			$posLeft = strpos($description, '<img ', $search_pos); 
			if ( $posLeft === false )
				break; 
			$posLeft = strpos($description, $delimeterLeft, $posLeft) + strlen($delimeterLeft); 
			$posRight = strpos($description, $delimeterRight, $posLeft); 
			if ( $posRight === false )
				break;
			$search_pos = $posLeft;
			$url_found = true;
			$url = substr($description, $posLeft, $posRight - $posLeft);
			if ( !is_integer( strpos($url, '://') ) && !is_integer( strpos($url, 'data:image/')) ) {
				$description = substr_replace($description, convert_image_to_data_url($url, true), $posLeft, $posRight - $posLeft);
			}
		}
		$description = str_ireplace('<script', '<!--', $description);
		$description = str_ireplace('</script', '-->', $description);
		
		$description = str_ireplace('<embed', '<!--', $description);
		$description = str_ireplace('</embed', '-->', $description);

		$description = str_ireplace('<object', '<!--', $description);
		$description = str_ireplace('</object', '-->', $description);

		$description = str_ireplace('<form', '<div', $description);
		$description = str_ireplace('</form', '</div', $description);
		$description = str_ireplace('<frame', '<param', $description);
		return $description;
	}

	function get_vote_info()
	{
		$data = make_api_request('get_stock_vote_info', array('stockid' => $this->stockid) );
		if ( $data['success'] )
			return $data['values'];
		else
			return false;
	}
}

global $months;
$months = array();

function day_callback($aLabel) 
{
	global $months;
	return $months[(int)$aLabel];
}

function price_callback($aLabel) 
{
	return '$'.number_format($aLabel, 2);
}

?>		
