<!-- load jQuery -->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>

<!-- load Galleria -->
<script src="<?= $conf_include_path; ?>galleria/galleria-1.2.9.min.js"></script>

<style>
	#galleria {
		height:768px;    /* defines a static gallery height */
		max-width:1024px; /* defines a responsive width */
	}
</style>

<?php
	write_log_db('album', 'Album: '. $_GET['detail'], 'ID: '. $_SESSION['id'], 'album.php');

    $sql = 'SELECT a.*, l.location_desc, l.lat_long, l.zoom
    FROM albums a LEFT JOIN locations l ON l.location_id = a.location
    WHERE a.album_id = '. $_GET['detail'];
    
    $sel_album = my_query($sql, $conex);
    
    $arr_album = my_fetch_array($sel_album);
    
	$zoom = $arr_album['zoom'] - 1;
    $map_src = 'http://maps.googleapis.com/maps/api/staticmap?center='. $arr_album['lat_long'] .'&zoom='. $zoom .'&size=800x600&maptype=roadmap&sensor=false';

    $sql = 'SELECT p.photo_id, p.title, p.photo_datetime, p.location_id, p.cam_make, p.cam_model, p.sspeed, p.aperture, p.ISO,
    i.img_type, i.file_name, i.path, i.img_w, i.img_h
    FROM photos p 
    INNER JOIN album_photo ap  ON ap.photo_id = p.photo_id
    INNER JOIN images i        ON  i.photo_id = p.photo_id
	INNER JOIN users_albums up ON ap.album_id = up.album_id
                              AND up.user_id = '. $_SESSION['id'] .'
    WHERE ap.album_id = '. $_GET['detail'] .'
	ORDER BY p.title';

    $sel_photos = my_query($sql, $conex);
    
    $arr_photos = array();
    while($record = my_fetch_array($sel_photos)) {
        $arr_photos[$record['photo_id']]['info'] = array('title' => $record['title']
                                                        ,'date' => $record['photo_datetime']
                                                        ,'cam_make' => $record['cam_make']
                                                        ,'cam_model' => $record['cam_model']
                                                        ,'sspeed' => $record['sspeed']
                                                        ,'aperture' => $record['aperture']
                                                        ,'ISO' => $record['ISO']);
                                                        
        $arr_photos[$record['photo_id']][$record['img_type']] = array('file_name' => $record['file_name']
                                                                     ,'path' => $record['path']
                                                                     ,'w' => $record['img_w'] ? $record['img_w'] : ''
                                                                     ,'h' => $record['img_w'] ? $record['img_w'] : '');
                                                        
        ;
    
    }
    
?>
<div id="galleria">
    <?php
    foreach($arr_photos as $photo_id => $arr_1_photo) {
		$aperture = $arr_1_photo['info']['aperture'] ? '; A: '. $arr_1_photo['info']['aperture'] : '';
		$speed = $arr_1_photo['info']['sspeed'] ? '; S: '. $arr_1_photo['info']['sspeed'] : '';
		
		if($arr_1_photo['info']['cam_make'] || $arr_1_photo['info']['cam_model'])
			$camera = $arr_1_photo['info']['cam_make'] .' '. $arr_1_photo['info']['cam_model'];
		
		$description = $camera . $speed . $aperture;
    ?>  
	<a href="<?= $arr_1_photo['med']['path'] .'/'. $arr_1_photo['med']['file_name']; ?>">
		<img src="<?= $arr_1_photo['thumb']['path'] .'/'. $arr_1_photo['thumb']['file_name']; ?>"
			 data-big="<?= $arr_1_photo['full']['path'] .'/'. $arr_1_photo['full']['file_name']; ?>"
			 data-title="<?= $arr_1_photo['info']['title'] .' ('. $photo_id .')'; ?>"
			 data-description="<?= $description; ?>">
	</a>
    <?php
    }   //  foreach($arr_photos as $photo_id => $arr_1_photo) {
    ?>
	<a href="<?= $map_src; ?>">
		<img src="<?= $map_src; ?>"
			 data-big="<?= $map_src; ?>"
			 data-title="Mapa">
	</a>
</div>
<br>
<!--<span align="center">
<img src="<?= $map_src; ?>"></span>-->
<script>

    // Load the Twelve theme
    Galleria.loadTheme('<?= $conf_include_path; ?>galleria/themes/twelve/galleria.twelve.min.js');

    // Initialize Galleria
    Galleria.run('#galleria', {
		transition: 'fade',
		imageCrop: false,
		thumbCrop: false,
		fullscreenCrop: false,
		preload: 4
	});

</script>
