<?php
if($_POST) {
	$large_path = 'data/large/'. $_POST['folder'];
	$small_path = 'data/small/'. $_POST['folder'];
	$thumb_path = 'data/thumb/'. $_POST['folder'];

	$photo_dir = opendir($large_path);
	
	$file_date = new date_time($_POST['date']);
	$year = $file_date->year;

	if($photo_dir) {
		#Insert album information in database.
		$arr_album_ins = array('title' => $_POST['album_title']
							  ,'description' => $_POST['album_description']
							  ,'keywords' => $_POST['keywords']
							  ,'album_date' => $file_date->datetime
							  ,'location' => $_POST['place']
							  ,'album_folder' => $_POST['folder']);
	
		$album_id = insert_array_db('albums', $arr_album_ins, true);
		
		$arr_album_place_ins = array('album_id' => $album_id, 'location_id' => $_POST['place']);
		insert_array_db('album_location', $arr_album_place_ins, false);
		
		$arr_album_user_ins = array('album_id' => $album_id, 'user_id' => '123');
		insert_array_db('users_albums', $arr_album_user_ins, false);
		
		$array_extensions = array('jpg', 'gif', 'png', 'jpeg', 'jpe');
		
		?>
		<table border="0" cellpadding="3" cellspacing="2">
		  <tr>
			<td></td>
			<td>Fichero Original</td>
			<td>tamaño</td>
			<td>medio</td>
			<td>icono</td>
			<td>EXIF</td>
		  </tr>
		<?php
		
		$arr_dir = scandir($large_path);
		foreach($arr_dir as $file) {
			$uploadFilename = $large_path .'/'. $file;
			$extension = getExtension($file);
			
			if(in_array($extension, $array_extensions)) {
				
				$im = create_image($uploadFilename, $extension);
				$width = imagesx($im);
				$height = imagesy($im);
				imagedestroy($im);
				
				# get exif info from picture
				if(function_exists('exif_read_data')) {
					$arr_exif = @exif_read_data($uploadFilename, 'IFD0', true);
					//$arr_exif['EXIF'] = exif_read_data($uploadFilename, 'EXIF', true);
				}
				$exif_date_time = str_replace(':', '-', substr($arr_exif['IFD0']['DateTime'], 0, 10)) . substr($arr_exif['IFD0']['DateTime'], 10);
				$odate = new date_time($exif_date_time);
				
				if($odate->datetime == '0000-00-00 00:00:00')
					$exif_date_time = $file_date->datetime;
				;
				
				# insert the photo information
				$arr_ins_photo = array('title' => $file
									  ,'photo_datetime' => $exif_date_time
									  ,'description' => $_POST['album_tilte']
									  ,'location_id' => $_POST['place']
									  ,'cam_make' => $arr_exif['IFD0']['Make']
									  ,'cam_model' => $arr_exif['IFD0']['Model']
									  ,'sspeed' => $arr_exif['EXIF']['ExposureTime']
									  ,'aperture' => $arr_exif['EXIF']['FNumber']
									  ,'ISO' => $arr_exif['EXIF']['ISOSpeedRatings']
									  );
					
				$photo_id = insert_array_db('photos', $arr_ins_photo, true);
				
				unset($arr_exif);
				
				# insert the image information
				$arr_ins_img = array('photo_id' => $photo_id
									,'file_name' => $file
									,'path' => $large_path
									,'img_w' => $width
									,'img_h' => $height
									,'file_type' => $extension
									,'file_datetime' => $file_date->datetime
									,'img_type' => 'full');
				
				echo '<tr><td><img src="'. $thumb_path .'/'. $file .'"></td><td>'. $large_path .'/'. $file .'</td>';
				echo '<td>'. $width .'x'. $height .'</td>';
				
				# get the thumb and med image information
				$small_file = $small_path .'/'. $file;
				$thumb_file = $thumb_path .'/'. $file;
				
				$im = create_image($small_file, $extension);
				$width = imagesx($im);
				$height = imagesy($im);
				imagedestroy($im);
				
				echo '<td>'. $width .'x'. $height .'</td>';

				$arr_ins_med = array('photo_id' => $photo_id
									,'file_name' => 'med_' . $file
									,'path' => $small_path
									,'img_w' => $width
									,'img_h' => $height
									,'file_type' => $extension
									,'file_datetime' => $file_date->datetime
									,'img_type' => 'med');
									
				$im = create_image($thumb_file, $extension);
				$width = imagesx($im);
				$height = imagesy($im);
				imagedestroy($im);
				
				echo '<td>'. $width .'x'. $height .'</td>';

				$arr_ins_thumb = array('photo_id' => $photo_id
									  ,'file_name' => 'thumb_' . $file
									  ,'path' => $thumb_path
									  ,'img_w' => $width
									  ,'img_h' => $height
									  ,'file_type' => $extension
									  ,'file_datetime' => $file_date->datetime
									  ,'img_type' => 'thumb');
				
				insert_array_db('images', $arr_ins_img, false);
				insert_array_db('images', $arr_ins_med, false);
				insert_array_db('images', $arr_ins_thumb, false);
				
				# insert photo-album relation
				$arr_photo_album_ins = array('photo_id' => $photo_id
											,'album_id' => $album_id);
											
				insert_array_db('album_photo', $arr_photo_album_ins, false);
				
				# set first picture as album cover
				if($first) {
					$first = false;
					$arr_upd_album = array('photo_cover_id' => $photo_id);
					update_array_db('albums', 'album_id', $album_id, $arr_upd_album);
				}
				
				echo '<td>Make:'. $arr_ins_photo['cam_make'] .'<br>Model: '. $arr_ins_photo['cam_model'] .'<br>S: '. $arr_ins_photo['sspeed'] .'<br>A: '. $arr_ins_photo['aperture'] .'</td></tr>';
			
			}	//	if(in_array($extension, $array_extensions)) {
		}	//	foreach($arr_dir as $file) {
?>
		</table>
<?php
	}	//	if($photo_dir) {
	closedir($photo_dir);
}	//	if($_POST) {


function getExtension($str) {
	$i = strrpos($str,".");
	if (!$i) { return ""; } 
	$l = strlen($str) - $i;
	$ext = substr($str,$i+1,$l);
	return strtolower($ext);
}

function get_new_size($w, $h, $max_w, $max_h) {
	if($w > $max_w) {
		$new_w = $max_w;
		$new_h = $new_w*($h/$w);
		# if new height is still larger than allowed, reduce furhter
		if($new_h > $max_h) {
			$new_h = $max_h;
			$new_w = $new_h*($w/$h);
		}
	}
	else {
		$new_w = $w;
		if($h > $max_h) {
			$new_h = $max_h;
			$new_w = $new_h*($w/$h); //$new_w*($max_h/$h)
		}
		else
			$new_h = $h;
	}

	return array('w' => round($new_w), 'h' => round($new_h));
}

function create_image($file, $extension){
	switch($extension) {
		case 'jpg': case 'jpeg': case 'jpe':
			$im = @imagecreatefromjpeg($file);
		break;
		case 'gif':
			$im = @imagecreatefromgif($file);
		break;
		case 'png':
			$im = @imagecreatefrompng($file);
		break;
		default:
			$im = false;
	}
	return $im;
}

?>
<table id="main_wrapper">
  <tr>
    <td id="main_form" width="50%" valign="top"><form name="album_form" id="album_form" method="post" action="">
        <table cellpadding="4" cellspacing="4">
          <tr>
            <td align="right"><p>Título</p></td>
            <td><input type="text" class="input_normal" name="album_title" id="album_title" /></td>
          </tr>
          <tr>
            <td align="right"><p>Descripci&oacute;n</p></td>
            <td><input type="text" class="input_normal" name="description" id="description" /></td>
          </tr>
          <tr>
            <td align="right"><p>Ubicaci&oacute;n</p></td>
            <td>
			<?php
			$parameters = array('table' => 'locations', 'code_field' => 'location_id', 'desc_field' => 'location_desc'
							   ,'name' => 'place', 'class' => 'input_normal', 'order' => 'location_desc');
			 print_combo_db ($parameters);
			?>
			&nbsp;&nbsp;<a href="<?= $conf_main_page; ?>?mod=home&view=new_place">Nuevo</a></td>
          </tr>
          <tr>
            <td align="right"><p>Plabras clave</p></td>
            <td><input type="text" class="input_normal" name="keywords" id="keywords" /></td>
          </tr>
          <tr>
            <td align="right"><p>carpeta</p></td>
            <td><input type="text" class="input_normal" name="folder" id="folder" /></td>
          </tr>
          <tr>
            <td align="right"><p>fecha</p></td>
            <td><input type="text" class="input_normal" name="date" id="date" /></td>
          </tr>
          <tr>
            <td align="right">&nbsp;</td>
            <td><input type="submit" class="button" value=" Crear álbum " /></td>
          </tr>
        </table>
      </form></td>
    <td id="secondary_form" width="50%" valign="top"><table>
        <tr>
          <td>Asignar a usuarios: <br><?php
		  $arr_users = dump_table('users', 'user_id', 'username', ' WHERE user_id <> 123');
		  foreach($arr_users as $user_id => $username) {
			?>
			<input type="check" name="u<?= $user_id; ?>"> <?= $username; ?><br>
			<?php
		  }
		  ?></td>
        </tr>
        <tr>
          <td>Asignar a lugares</td>
        </tr>
      </table></td>
  </tr>
</table>
