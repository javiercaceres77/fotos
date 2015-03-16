delete from images 
where photo_id in (
	select photo_id
	from album_photo
	where album_id = 13);
	
	
delete from photos
where photo_id in (
	select photo_id
	from album_photo
	where album_id = 13);

delete from album_photo 
where album_id = 13;

delete from album_location
where album_id = 13;

delete from users_albums
where album_id = 13;

delete from albums
where album_id = 13;