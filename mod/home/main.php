<?php

$album_arr = get_albums($_SESSION['id']);

$arr_filters = array();
foreach($album_arr as $album) {
	$odate = new my_date($album['album_date']);
	
	if($arr_filters['date'][$odate->year])
		$arr_filters['date'][$odate->year]++;
	else
		$arr_filters['date'][$odate->year] = 1;
	
	if($arr_filters['place'][$album['location']])
		$arr_filters['place'][$album['location']]['num_albums']++;
	else {
		$arr_filters['place'][$album['location']]['num_albums'] = 1;
		$arr_filters['place'][$album['location']]['desc'] = $album['location_desc'];
	}
}
?>

<div id="filters_wrapper" class="indented">
  <form name="filters_form">
    <table border="0" width="800">
      <tr>
        <td id="dates_filter"><?= ucfirst(dates); ?>: 
        <?php
		foreach($arr_filters['date'] as $year => $num_albums)
			echo '<a href="'. $conf_main_page .'?f=date&val='. $year .'" title="'. $num_albums .'">'. $year .'</a>&nbsp&nbsp';
		?>
  </td>
       <!-- <td><select class="input_small">
            <option>Más fechas</option>
          </select></td>-->
      </tr>
    <!--  <tr>
        <td id="places_filter"><?= ucfirst(places); ?>: 
        <?php
		foreach($arr_filters['place'] as $location_id => $arr_location)
			echo '<a href="'. $conf_main_page .'?f=place&val='. $location_id .'" title="'. $arr_location['num_albums'] .'">'. $arr_locations['desc'] .'</a>&nbsp&nbsp';
		?>
        <td><select class="input_small">
            <option>Otros lugares</option>
          </select></td>
      </tr>
      <tr>
        <td id="search_box"><?= ucfirst(search); ?>
          :
          <input name="search" type="text" class="input_small" id="user" maxlength="60" />
          <input type="button" class="button_small" value="  <?= GO; ?>  " /></td>
        <td></td>
      </tr>-->
    </table>
  </form>
</div>
<div id="albums_wrapper">
  <table width="100%" border="0" cellspacing="10" cellpadding="10">
    <tr>
      <?php


$row = 0;
foreach($album_arr as $album) {
	$odate = new my_date($album['album_date']);
	
	if($_GET['f'] == 'date' && $_GET['val'] != $odate->year)
		continue;
	
	if($row % 5 == 0)
		echo '</tr><tr>';
	$row++;
		$w = $album['img_w'] ? $album['img_w'] : '';
		$h = $album['img_h'] ? $album['img_h'] : '';
?>
      <td align="center"><a href="<?= $conf_main_page; ?>?mod=home&view=album&detail=<?= $album['album_id']; ?>">
      <img src="<?= $album['path'] .'/'. $album['file_name']; ?>" width="<?= $w; ?>" height="<?= $h; ?>" border="0" class="thin_border_picture" /></a><br />
        <?= $album['title']; ?>
        <br />
        <?= $odate->format_date('med'); ?>
        (<?= $album['num_photos']; ?>
        fotos)<br />
		<?= $album['location_desc']; ?></td>
      <?php
}

?>
    </tr>
  </table>
</div>
