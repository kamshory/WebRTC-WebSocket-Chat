<?php
$language_id = 'id';
if(!isset($language_res))
{
	$language_res = array();
}
if(!isset($language_res[$language_id]))
{
	$language_res[$language_id] = array();
}
if(!isset($language_list))
{
	$language_list = array();
}
$language_list[$language_id] = 'Indonesia';

$language_res[$language_id] = array(
'template_context_menu_post_1' => 
'<ul class="context-menu-post">
<li><a href="#edit-post-dialog" data-title="Ubah Postingan" data-rel="popup" data-location-async="lib.ajax/ajax-edit-post.php?post_id=%s" class="cm-edit"><span class="icon pencil"></span> Ubah Postingan</a></li>
<li><a href="#add-attachment-image" data-title="Tambah Gambar" data-rel="popup" data-location-async="lib.ajax/ajax-add-images.php?post_id=%s" class="cm-add-images"><span class="icon picture"></span> Tambah Gambar</a></li>
<li><a href="#add-attachment-image" data-title="Tambah Panorama"  data-rel="popup" data-location-async="lib.ajax/ajax-add-images.php?image_type=panorama&post_id=%s" class="cm-add-images"><span class="icon panorama"></span> Tambah Panorama</a></li>
<li><a href="#common-dialog" data-title="Bagikan Postingan" data-rel="popup" data-location-async="lib.ajax/ajax-share-post.php?post_id=%s" data-cookie="true" class="cm-share"><span class="icon share"></span> Bagikan Postingan</a></li>
<li><a href="#common-dialog" data-title="Permanent Link" data-rel="popup" data-location-async="lib.ajax/ajax-permalink-post.php?post_id=%s"  data-cookie="true" class="cm-permalink"><span class="icon link"></span> Tampilkan Permanent Link</a></li>
<li><a href="javascript:;" data-post-id="%s" class="cm-delete"><span class="icon sign-remove"></span> Hapus Postingan</a></li>
</ul>
',

/* postingan orang lain untuk saya */
'template_context_menu_post_2' => 
'<ul class="context-menu-post">
<li><a href="#common-dialog" data-title="Bagikan Postingan" data-rel="popup" data-location-async="lib.ajax/ajax-share-post.php?post_id=%s" data-cookie="true" class="cm-share"><span class="icon share"></span> Bagikan Postingan</a></li>
<li><a href="#common-dialog" data-title="Permanent Link" data-rel="popup" data-location-async="lib.ajax/ajax-permalink-post.php?post_id=%s"  data-cookie="true" class="cm-permalink"><span class="icon link"></span> Tampilkan Permanent Link</a></li>
<li><a href="javascript:;" data-post-id="%s" class="cm-spam"><span class="icon shield"></span> Laporkan Penyalahgunaan</a></li>
<li><a href="javascript:;" data-post-id="%s" class="cm-delete"><span class="icon sign-remove"></span> Hapus Postingan</a></li>
<li><a href="#common-dialog" data-title="Laporkan Postingan" data-rel="popup" data-location-async="lib.ajax/ajax-report-abuse.php?post_id=%s" data-cookie="true" class="cm-report-abuse"><span class="icon shield"></span> Laporkan Postingan</a></li>
</ul>
',

/* tidak ada hubungan dengan saya */
'template_context_menu_post_3' =>  
'<ul class="context-menu-post">
<li><a href="#common-dialog" data-title="Bagikan Postingan" data-rel="popup" data-location-async="lib.ajax/ajax-share-post.php?post_id=%s" data-cookie="true" class="cm-share"><span class="icon share"></span> Bagikan Postingan</a></li>
<li><a href="#common-dialog" data-title="Permanent Link" data-rel="popup" data-location-async="lib.ajax/ajax-permalink-post.php?post_id=%s" data-cookie="true" class="cm-permalink"><span class="icon link"></span> Tampilkan Permanent Link</a></li>
<li><a href="#common-dialog" data-title="Laporkan Postingan" data-rel="popup" data-location-async="lib.ajax/ajax-report-abuse.php?post_id=%s" data-cookie="true" class="cm-report-abuse"><span class="icon shield"></span> Laporkan Postingan</a></li>
</ul>
',

'template_context_menu_reply_1' => 
'<ul class="context-menu-post">
<li><a href="#edit-post-dialog" data-title="Ubah Balasan" data-rel="popup" data-location-async="lib.ajax/ajax-edit-post.php?post_id=%s" class="cm-edit"><span class="icon pencil"></span> Ubah Balasan</a></li>
<li><a href="#add-attachment-image" data-title="Tambah Gambar" data-rel="popup" data-location-async="lib.ajax/ajax-add-images.php?post_id=%s" class="cm-add-images"><span class="icon picture"></span> Tambah Gambar</a></li>
<li><a href="#add-attachment-image" data-title="Tambah Panorama"  data-rel="popup" data-location-async="lib.ajax/ajax-add-images.php?image_type=panorama&post_id=%s" class="cm-add-images"><span class="icon panorama"></span> Tambah Panorama</a></li>
<li><a href="javascript:;" data-post-id="%s" class="cm-delete"><span class="icon sign-remove"></span> Hapus Balasan</a></li>
</ul>
',

/* postingan orang lain untuk saya */
'template_context_menu_reply_2' => 
'<ul class="context-menu-post">
<li><a href="javascript:;" data-post-id="%s" class="cm-spam"><span class="icon shield"></span> Laporkan Penyalahgunaan</a></li>
<li><a href="javascript:;" data-post-id="%s" class="cm-delete"><span class="icon sign-remove"></span> Hapus Balasan</a></li>
<li><a href="#common-dialog" data-title="Laporkan Balasan" data-rel="popup" data-location-async="lib.ajax/ajax-report-abuse.php?post_id=%s" data-cookie="true" class="cm-report-abuse"><span class="icon shield"></span> Laporkan Balasan</a></li>
</ul>
',

/* tidak ada hubungan dengan saya */
'template_context_menu_reply_3' =>  
'<ul class="context-menu-post">
<li><a href="#common-dialog" data-title="Laporkan Balasan" data-rel="popup" data-location-async="lib.ajax/ajax-report-abuse.php?post_id=%s" data-cookie="true" class="cm-report-abuse"><span class="icon shield"></span> Laporkan Balasan</a></li>
</ul>
',

'template_reply_data' =>  
'<li data-post-id="%s" data-parent-id="%s">
	<div class="post-image-50"><a href="%s"><img src="%s"></a></div>
	<div class="reply-data">
		<div class="post-sender">
			<span class="post-time post-time-float-right" data-timestamp="%s">%s</span>
			<h3><a href="%s">%s</a></h3>
		</div>
		<div class="reply-content">%s</div>
	</div>
	<div class="post-respond"></div>
	<div class="post-action" disabled="disabled">
		<span class="post-more-action"><a href="javascript:;" class="link-disabled"><span class="icon angle-down"></span></a></span>
		<span class="post-reply"><a href="javascript:;" class="link-disabled"><span class="icon reply"></span> <span class="action-text">Balas</span></a></span>
		<span class="post-like"><a href="javascript:;" class="link-disabled"><span class="icon thumb-up"></span> <span class="action-text">Suka</span></a></span>
		<span class="post-dislike"><a href="javascript:;" class="link-disabled"><span class="icon thumb-down"></span> <span class="action-text">Tidak Suka</span></a></span>
		<span class="post-neutral"><a href="javascript:;" class="link-disabled"><span class="icon circle"></span> <span class="action-text">Netral</span></a></span>
   </div>
</li>
',


'template_reply_form' =>  
'<form class="replyform" action="" method="post" enctype="multipart/form-data">
<div class="post-image-50">
	<img src="%s">
</div>
<div class="reply-block">
	<div><textarea spellcheck="false" name="replytext" class="reply-text autoheight" data-min-rows="1" style="height:43px"></textarea></div>
	<div class="reply-attachment reply-attachment-parent-%s"><ul class="attachment-list"></ul></div>
	<div class="reply-geolocation reply-geolocation-parent-%s"><ul class="geolocation-list"></ul></div>
	<div class="reply-button-area">
		<input type="submit" name="reply" class="reply-button" value="Balas">
		<a href="javascript:;" title="Balas" class="reply-button-icon"><span class="icon arrow-circle-alt-up"></span></a>
		<a href="javascript:;" title="Tambah Gambar" class="reply-add-attachment" data-selector=".reply-attachment-parent-%s"><span class="icon picture"></span></a>
		<a href="javascript:;" title="Tambah Panorama" class="reply-add-attachment-360" data-selector=".reply-attachment-parent-%s"><span class="icon panorama"></span></a>
		<a href="javascript:;" title="Tambah Lokasi" class="post-add-geolocation" data-selector=".reply-geolocation-parent-%s"><span class="icon geolocation"></span></a>
		<a href="javascript:;" title="Tambah Emoji" class="post-add-emoji"><span class="icon emoji"></span></a>
		<a href="javascript:;" title="Tambah Sketsa" onclick="window.open(\'apps/drawing/?reply=%s\'); return false;" class="post-add-drawing" data-selector=".post-drawing"><span class="icon flash"></span></a>
	</div>
</div>
</form>
',

'template_post_form' =>  
'<form class="postform" action="" method="post" enctype="multipart/form-data">
<div class="post-image-50">
	<img src="%s">
</div>
<div class="post-block">
	<div><textarea spellcheck="false" name="posttext" class="post-text autoheight" data-min-rows="1" style="height:43px"></textarea></div>
	<div class="post-button-area"><input type="submit" name="post" class="post-button" value="Balas"></div>
</div>
</form>
',
'app_name' => 'Planetbiru',
/*'app_description' => 'Planetbiru adalah media sosial Indonesia untuk para petualang yang ingin membagikan pengalamannya kepada siapa saja. Buat akun sekarang juga dan bagikan pengalaman seru Anda. Gratis untuk selamanya.',
*/
/*'app_description' => 'Planetbiru adalah media sosial dengan dukungan Panorama yang memberikan Anda pengalaman baru dalam berbagi. Buat akun Anda sekarang juga dan bagikan pengalaman seru Anda. Gratis untuk selamanya.',
*/
'app_description' => 'Masih jaman ya gambar 2 dimensi? Planetbiru adalah media sosial asli Indonesia dengan dukungan Panorama 360 derajat yang memberikan Anda pengalaman baru dalam berbagi. Buat akun Anda sekarang juga dan bagikan pengalaman seru Anda. Gratis untuk selamanya.',
'app_company_name'=>'Planetbiru',
'app_first_year'=>'2008',

'title_chat_user_fmt'=> 'Ngobrol Dengan %s',
'title_posts_user_fmt'=> 'Postingan %s',
'title_images_user_fmt'=> 'Gambar %s',
'title_panorama_user_fmt'=> 'Panorama %s',
'title_attachments_user_fmt'=> 'Lampiran %s',
'title_locations_user_fmt'=> 'Lokasi %s',
'title_avatar_user_fmt'=>'Avatar %s',
'title_about_user_fmt'=>'Tentang %s',
'title_referal_user_fmt'=>'%s: Mari Bergabung dengan Planetbiru',

'title_reset_password'=>'Reset Passoword',
'title_login'=>'Masuk',
'title_attachment'=>'Lampiran',
'title_image'=>'Gambar',
'title_panorama'=>'Panorama',
'title_content_search'=>'Pencarian Postingan',
'title_post_search'=>'Pencarian',
'title_people_search'=>'Pencarian Orang',
'title_notification'=>'Pemberitahuan',
'title_thread'=>'Postingan Terbaru',
'title_help'=>'Bantuan',
'title_home'=>'Planetbiru',
'title_member_location'=>'Lokasi',
'title_member_avatar'=>'Avatar',
'title_member_images'=>'Gambar',
'title_member_attachment'=>'Lampiran',
'title_404'=>'Halaman Tidak Ditemukan',
'title_403'=>'Terlarang',
'title_about'=>'Tentang Planetbiru',
'title_avatar'=>'Avatar',
'title_background'=>'Pengaturan Latar',
'title_camera'=>'Daftar Kamera',
'title_image_not_found'=>'Gambar Tidak Ditemukan',
'title_member_not_found'=>'Pengguna Tidak Ditemukan',
'title_member_referal'=>'Referal Planetbiru',
'title_posts_not_found'=>'Postingan Tidak Ditemukan',
'title_register'=>'Mendaftar',
'title_registered'=>'Sudah Terdaftar',
'title_setting'=>'Pengaturan',
'title_account_setting'=>'Pengaturan Akun',
'title_application_setting'=>'Pengaturan Aplikasi',
'title_share_not_logged_in'=>'Bagikan Postingan',
'title_referal'=> 'Mari Bergabung di Planetbiru',
'title_invite_friend'=>'Undang Teman',
'title_statistic'=>'Statistik',
'title_language'=>'Bahasa',
'title_emoji'=>'Emoji',
'title_user'=>'Pengguna',
'title_user_country'=>'Pengguna Planetbiru dari %s',
'title_application'=>'Aplikasi',
'title_game'=>'Permainan',
'title_shake'=>'Guncang',

'txt_specified_user_not_registered'=>'Pengguna yang dimaksud tidak ditemukan di database. Sistem tidak menemukan informasi apapun tentang',
'txt_member_not_found_available'=>'Pengguna yang Anda maksud tidak ditemukan. ID ini dapat Anda gunakan jika Anda berkenan.',
'txt_member_not_found_not_available'=>'Pengguna tidak ditemukan tetapi ID ini tidak tersedia sekarang jika Anda ingin karena sistem menahan ID ini untuk beberapa alasan.',
'txt_report_reason'=>'Alasan Melaporkan',
'txt_report_message_to_admin'=>'Tuliskan pesan Anda kepada administrator kami.',

'txt_you_already_registered'=> 'Anda Telah Terdaftar',
'txt_original_posts'=>'Postingan Asli',
'txt_content_type'=>'Tipe',
'txt_content_source'=>'Sumber',
'txt_attachment_time'=>'Waktu Pengambilan',
'txt_show_posts'=>'Tampilkan Postingan',
'txt_attachment_information'=>'Informasi Lampiran',
'txt_delete_image'=>'Hapus Gambar',
'txt_delete_location'=>'Hapus Lokasi',
'txt_delete_attachment'=>'Hapus Lampiran',
'txt_report_image'=>'Laporkan Gambar',
'txt_report_attachment'=>'Laporkan Lampiran',
'txt_image_format'=>'Format Gambar',
'txt_width'=>'Lebar',
'txt_height'=>'Tinggi',
'txt_original'=>'Asli',
'txt_file_size'=>'Ukuran File',
'txt_time_taken'=>'Waktu Pengambilan',
'txt_time_upload'=>'Waktu Upload',
'txt_camera_model'=>'Model Kamera',
'txt_latitude'=>'Lintang',
'txt_longitude'=>'Bujur',
'txt_altitude'=>'Ketinggian',
'txt_md5_file'=>'MD5 File',
'txt_md5_file_original'=>'MD5 File Asli',
'txt_image_info_taken_not_exists'=>'Gambar tidak berisi informasi pengambilan.',
'txt_image_info_location_not_exists'=>'Gambar tidak berisi informasi lokasi.',
'txt_common_dialog' => 'Dialog Umum',
'txt_emoji_dialog' => 'Masukkan Emoji',
'txt_edit_post' => 'Ubah Postingan',
'txt_edit_post_history' => 'Riwayat',
'txt_image_information' => 'Informasi Gambar',
'txt_location_information' => 'Informasi Lokasi',
'txt_enlarge_image' => 'Perbesar Gambar',
'txt_like'=>'Suka',
'txt_dislike'=>'Tidak Suka',
'txt_neutral'=>'Netral',
'txt_reply'=>'Balas',
'txt_edited'=>'Telah diubah',
'txt_profile'=>'Profil',
'txt_thread'=>'Postingan Terbaru',
'txt_show'=>'Tampilkan',
'txt_other'=>'Lainnya',
'txt_add_attachment'=>'Tambah Lampiran',
'txt_add_image'=>'Tambah Gambar',
'txt_add_image_360'=>'Tambah Panorama',
'txt_compress_image_360'=>'Kompres Panorama',
'txt_enable_multiple_chat'=>'Obrolan Simultan',
'txt_what_is_panorama'=>'Apa Itu Panorama',
'txt_emoji' => 'Emoji',
'txt_insert_emoji' => 'Masukkan Emoji',
'txt_emoji_list' => 'Daftar Emoji',
'txt_emoji_category' => 'Kategori Emoji',
'txt_user' => 'Pengguna',
'txt_member' => 'Anggota',


'txt_general'=>'Umum',
'txt_camera'=>'Kamera',
'txt_camera_list'=>'Daftar Kamera',
'txt_help'=>'Bantuan',
'txt_all_rights_reserved'=>'All rights reserved',
'txt_powered_by'=>'Powered by',

'txt_man'=>'Laki-Laki',
'txt_woman'=>'Perempuan',
'txt_not_human'=>'Organisasi',

'txt_gender_m'=>'Laki-Laki',
'txt_gender_w'=>'Perempuan',
'txt_gender_o'=>'Organisasi',

'txt_name'=>'Nama',
'txt_username'=>'Username',
'txt_email'=>'Email',
'txt_phone'=>'Telepon',
'txt_website'=> 'Situs Web',
'txt_language'=> 'Bahasa',
'txt_device_language'=> 'Bahasa Perangkat',
'txt_password'=>'Password',
'txt_old_password'=>'Password Lama',
'txt_new_password'=>'Password Baru',
'txt_retype_password'=>'Ulangi Password',
'txt_gender'=>'Jenis Kelamin',
'txt_birth_place'=>'Tempat Lahir',
'txt_birth_day'=>'Tanggal Lahir',
'txt_country'=>'Negara',
'txt_select_country'=>'Pilih Negara',
'txt_all_country'=>'Semua Negara',
'txt_state'=> 'Provinsi',
'txt_select_state'=> 'Pilih Provinsi',
'txt_all_state'=> 'Semua Provinsi',
'txt_city'=> 'Kota',
'txt_select_city'=> 'Pilih Kota',
'txt_all_city'=> 'Semua Kota',
'txt_subdistrict'=> 'Kecamatan',
'txt_village'=> 'Desa',

'txt_option_select_country'=>'- Pilih Negara -',
'txt_option_select_state'=>'- Pilih Provinsi -',
'txt_option_select_city'=>'- Pilih Kota -',

'txt_create_account'=>'Buat Akun',
'txt_home'=>'Depan',
'txt_message'=>'Pessan',
'txt_notification'=>'Pemberitahuan',
'txt_invite_friend'=>'Undang Teman',
'txt_show_all'=>'Tampilkan Semua',

'txt_menu'=>'Menu',

'txt_resolution'=>'Resolusi',
'txt_dimension'=>'Dimensi',
'txt_weight'=>'Berat',
'txt_number_of_image'=>'Jumlah Gambar',
'txt_image'=>'Gambar',

'txt_invite_your_friend'=> 'Undang Teman Anda',

'txt_placeholder_email'=>'Email',
'txt_placeholder_username'=>'Username',
'txt_placeholder_username_alt'=>'Username/Email',
'txt_placeholder_invitation'=>'Tulis pesan Anda di sini',
'txt_placeholder_search'=>'Pencarian',
'txt_placeholder_name'=>'Nama',
'txt_placeholder_username_desire'=>'ID Diinginkan',
'txt_placeholder_password'=>'Password',
'txt_placeholder_status'=> 'Tulis apa yang Anda pikirkan di sini',
'txt_placeholder_connet'=> 'Tulis komentar Anda',
'txt_placeholder_chat'=> 'Tulis pesan Anda...',

'txt_image_list'=>'Daftar Gambar',
'txt_image_not_found'=>'Gambar tidak ditemukan.',
'txt_attachment_not_found'=>'Lampiran tidak ditemukan.',

'txt_day_mon'=>'Sen',
'txt_day_tue'=>'Sel',
'txt_day_wed'=>'Rab',
'txt_day_thu'=>'Kam',
'txt_day_fri'=>'Jum',
'txt_day_sat'=>'Sab',
'txt_day_sun'=>'Min',

'txt_day_monday'=>'Senin',
'txt_day_tuesday'=>'Selasa',
'txt_day_wednesday'=>'Rabu',
'txt_day_thursday'=>'Kamis',
'txt_day_friday'=>'Jum at',
'txt_day_satyrday'=>'Sabtu',
'txt_day_sunday'=>'Minggu',

'txt_month_jan'=>'Jan',
'txt_month_feb'=>'Feb',
'txt_month_mar'=>'Mar',
'txt_month_apr'=>'Apr',
'txt_month_may'=>'Mei',
'txt_month_jun'=>'Jun',
'txt_month_jul'=>'Jul',
'txt_month_aug'=>'Agu',
'txt_month_sep'=>'Sep',
'txt_month_oct'=>'Okt',
'txt_month_nov'=>'Nop',
'txt_month_dec'=>'Des',

'txt_month_january'=>'Januari',
'txt_month_february'=>'Februari',
'txt_month_march'=>'Maret',
'txt_month_april'=>'April',
'txt_month_may'=>'Mei',
'txt_month_june'=>'Juni',
'txt_month_july'=>'Juli',
'txt_month_august'=>'Agustus',
'txt_month_sepember'=>'September',
'txt_month_october'=>'Oktober',
'txt_month_november'=>'Nopember',
'txt_month_december'=>'Desember',

'txt_select_image'=>'Pilih',
'txt_save_image'=>'Simpan',
'txt_crop_image_center'=>'Potong di Tengah',
'txt_move_crop_up'=>'Geser ke Atas',
'txt_move_crop_down'=>'Geser ke Bawah',
'txt_move_crop_right'=>'Geser ke Kanan',
'txt_move_crop_left'=>'Geser ke Kiri',
'txt_rotate_cw'=>'Putar Ke Kanan',
'txt_rotate_ccw'=>'Putar Ke Kiri',
'txt_flip_horizontal'=>'Cerminkan Horizontal',
'txt_flip_vertical'=>'Cerminkan Vertikal',
'txt_touch_frame_from_inside'=> 'Menyentuh Bingkai dari Dalam',
'txt_touch_frame_from_outside'=> 'Menyentuh Bingkai dari Luar',

'txt_message_not_exists'=>'Tidak ada pesan.',
'txt_notification_not_exists'=>'Tidak ada pemberitahuan.',
'txt_show_image_before'=>'Tampilkan Gambar Sebelumnya',
'txt_your_location'=>'Lokasi Anda',

'txt_login'=>'Login',
'txt_last_login'=> 'Online',
'txt_age'=> 'Umur',
'txt_years'=>'tahun',
'txt_years_old'=>'Tahun',
'txt_in_order_say_hi'=>'untuk mengirimkan sesuatu kepada',
'txt_within'=>'dalam',

'txt_just_now'=>'Baru Saja',
'txt_now'=>'Sekarang',
'txt_not_supported'=>'Tidak didukung',

'txt_vr_image_trigger_html'=> '<p></p><p><span class="icon icon-24 play"></span></p>',
'txt_vr_image_loading_status_html'=> '<p>Memuat...</p>',
'txt_show_compass'=> 'Tampilkan Kompas',
'txt_autoplay_360'=> 'Otomatis Mainkan Panorama',
'txt_autorotate_360'=> 'Otomatis Rotasi Panorama',
'txt_yes'=> 'Ya',
'txt_yes_recommended'=> 'Ya (<strong>Disarankan</strong>)',
'txt_no'=> 'Tidak',
'txt_no_recommended'=> 'Tidak (<strong>Disarankan</strong>)',
'txt_none'=> 'Tidak Ada',
'txt_rotate_right'=> 'Ke Kanan',
'txt_rotate_left'=> 'Ke Kiri',

'txt_report_abuse'=>'Laporkan Penyalahgunaan',
'txt_cb_on_copy'=>'Selengkapnya, baca',
'txt_show_latest_posts'=>'Tampilkan Postingan Terbaru',
'txt_show_older_posts'=> 'Tampilkan Postingan Sebelumnya',
'txt_show_older_notification'=> 'Tampilkan Notifikasi Sebelumnya',
'txt_show_older_location'=> 'Tampilkan Lokasi Sebelumnya',
'txt_show_older_attachment'=> 'Tampilkan Lampiran Sebelumnya',
'txt_show_older_panorama'=> 'Tampilkan Panorama Sebelumnya',
'txt_show_older_image'=> 'Tampilkan Gambar Sebelumnya',
'txt_show_older_post'=> 'Tampilkan Postingan Sebelumnya',
'txt_large_image'=> 'Large Image',
'txt_show_older_posts'=>'Tampilkan Postingan Lebih Lama',
'txt_large_image'=>'Gambar Besar',
'txt_user_of'=>'Pengguna Planetbiru',
'txt_people_who_like'=>'Orang yang Menyukai',
'txt_people_who_dislike'=>'Orang yang Tidak Menyukai',
'txt_say_something'=>'Katakan sesuatu kepada',
'txt_people_may_you_know'=>'Orang Yang Mungkin Anda Kenal',
'txt_new_registrant'=>'Pendaftar baru',


'txt_you_as_subject'=>'Anda',
'txt_you_as_object'=>'Anda',

'txt_send_email_to'=>'Kirim email ke',
'txt_open_url'=>'Buka URL',
'txt_loading_user_info'=>'Memuat informasi tentang',
'txt_loading_hashtag_info'=>'Mencari hashtag',
'txt_create_hashtag'=>'Buat hashtag %s',
'txt_hashtag_info_count'=>'Ada %s postingan dengan hashtag %s',
'txt_show_n_others'=>'Tampilkan %s Lainnya',
'txt_user_list'=>'Pengguna Planetbiru',
'txt_show_other'=>'Tampilkan Lainnya',
'txt_or'=>'Atau',
'txt_radar'=>'Radar',
'txt_shake'=>'Guncang',
'txt_search_setting'=>'Pengaturan Pencarian',

'txt_unit_kilometer'=>'km',
'txt_unit_meter'=>'meter',
'txt_unit_meters'=>'meter',
'txt_above_mean_sea_level'=>'dpl',

'msg_notif_110'=>'%s menyukai postingan di profil %s',
'msg_notif_111'=>'%s tidak menyukai postingan di profil %s',
'msg_notif_112'=>'%s menyukai balasan postingan di profil %s',
'msg_notif_113'=>'%s tidak menyukai balasan postingan di profil %s',
'msg_notif_120'=>'%s mengirimkan sesuatu di profil %s',
'msg_notif_121'=>'%s membalas postingan di profil %s',
'msg_notif_130'=>'%s menyebut Anda di sebuah postingan di profil %s',
'msg_notif_131'=>'%s menyebut Anda di sebuah komentar di profil %s',

'msg_report_abuse_10'=>'Postingan ini mengganggu saya',
'msg_report_abuse_11'=>'Postingan ini melecehkan saya',
'msg_report_abuse_12'=>'Postingan ini melecehkan orang lain',
'msg_report_abuse_13'=>'Postingan ini mengandung unsur SARA',
'msg_report_abuse_14'=>'Postingan ini mengandung unsur pornografi',
'msg_report_abuse_15'=>'Postingan ini mengandung unsur provokasi',
'msg_report_abuse_16'=>'Postingan ini mengandung unsur kebencian',
'msg_report_abuse_17'=>'Postingan ini mengandung unsur perjudian',
'msg_report_abuse_18'=>'Postingan ini mengandung unsur kekerasan',
'msg_report_abuse_19'=>'Postingan ini tidak seharusnya ada di Planetbiru',
'msg_report_abuse_20'=>'Gambar ini mengganggu saya',
'msg_report_abuse_21'=>'Gambar ini melecehkan saya',
'msg_report_abuse_22'=>'Gambar ini melecehkan orang lain',
'msg_report_abuse_23'=>'Gambar ini mengandung unsur SARA',
'msg_report_abuse_24'=>'Gambar ini mengandung unsur pornografi',
'msg_report_abuse_25'=>'Gambar ini mengandung unsur provokasi',
'msg_report_abuse_26'=>'Gambar ini mengandung unsur kebencian',
'msg_report_abuse_27'=>'Gambar ini mengandung unsur perjudian',
'msg_report_abuse_28'=>'Gambar ini mengandung unsur kekerasan',
'msg_report_abuse_29'=>'Gambar ini tidak layak ada di Planetbiru',
'msg_report_abuse_71'=>'Orang ini mengaku sebagai saya',
'msg_report_abuse_72'=>'Orang ini mengaku sebagai orang lain',
'msg_report_abuse_73'=>'Orang ini melakukan penipuan/pemerasan',
'msg_report_abuse_74'=>'Orang ini melakukan teror dan mengancam',
'msg_report_abuse_75'=>'Orang ini telah melukai hati saya',

'msg_never_made_post'=>'%s belum pernah membuat postingan.',
'msg_available_language'=>'Planetbiru tersedia dalam %s bahasa. Silakan pilih bahasa yang sesuai dengan Anda.',
'msg_referal'=>'Hai semuanya. Nama saya %s. Saya mengajak Anda untuk bergabung di Planetbiru. Planetbiru merupakan media sosial asli Indonesia khusus bagi para petualang dan para pelancong. Mari majukan Indonesia dengan mencintai dan menggunakan produk-produk asli Indonesia.',
'msg_you_already_registered'=>'Anda telah terdaftar di situs ini.',
'msg_status_saving' => 'Menyimpan...',
'msg_status_saved' => 'Tersimpan',
'msg_status_loading' => 'Memuat...',
'msg_status_loaded' => 'Termuat',
'msg_status_uploading' => 'Menugupload...',
'msg_status_uploaded' => 'Terupload',
'msg_registered_in_this_site'=> 'terdaftar di Planetbiru. Bergabunglah dengan Planetbiru untuk menjalin hubungan dengannya dan mengikuti semua aktivitasnya.',
'msg_registered_in_this_site_m'=> 'terdaftar di Planetbiru. Bergabunglah dengan Planetbiru untuk menjalin hubungan dengannya dan mengikuti semua aktivitasnya.',
'msg_registered_in_this_site_w'=> 'terdaftar di Planetbiru. Bergabunglah dengan Planetbiru untuk menjalin hubungan dengannya dan mengikuti semua aktivitasnya.',
'msg_registered_in_this_site_o'=> 'terdaftar di Planetbiru. Daftarkan organisasi Anda di Planetbiru dan buatlah hubungan sebanyak-banyaknya.',
'msg_you_never_upload_image'=>'Anda belum pernah mengupload gambar. Anda dapat mengambil gambar dari sumber eksternal. Salin URL gambar kemudian masukkan ke <strong>Background URL</strong>.',
'msg_change_post_history'=>'Riwayat Perubahan',
'msg_wait_upload_finish'=>'Silakan menunggu lampiran terkirim.',
'msg_confirm_delete_posts'=>'Apakah Anda yakin akan menghapus postingan ini?',
'msg_confirm_delete_reply'=>'Apakah Anda yakin akan menghapus balasan ini?',
'msg_confirm_delete_image'=>'Apakah Anda yakin akan menghapus gambar ini?',
'msg_confirm_delete_attachment'=> 'Apakah Anda yakin akan menghapus lampiran ini?',
'msg_confirm_delete_location'=> 'Apakah Anda yakin akan menghapus lokasi ini?',
'msg_confirm_delete_avatar'=> 'Apakah Anda yakin akan menghapus avatar ini?',
'msg_confirm_delete_all_notification'=>'Apakah Anda akan menghapus semua pemberitahuan?',
'msg_confirm_delete_read_notification'=>'Apakah Anda akan menghapus pemberitahuan yang telah dibaca?',
'msg_confirm_crop_avatar' => 'Apakah Anda yakin akan memotong gambar tepat di tengah?',
'msg_confirm_rotate_cw' => 'Apakah Anda yakin akan memutar gambar ini ke kanan?',
'msg_confirm_rotate_ccw' => 'Apakah Anda yakin akan memutar gambar ini ke kiri?',
'msg_confirm_flip_vertical' => 'Apakah Anda yakin akan mencerminkan gambar ini secara vertikal?',
'msg_confirm_flip_horizontal' => 'Apakah Anda yakin akan mencerminkan gambar ini secara horizontal?',
'msg_confirm_logout' => 'Apakah Anda yakin akan keluar?',

'msg_not_suppoted_date_tz_php'=>'Tidak didukung (lihat kode sumber dari date() untuk zona waktu tentang cara untuk menambahkan dukungan)',
'msg_position_unavailable'=>'Tidak dapat mendeteksi lokasi Anda. Apakah Anda ingin mencoba kembali?',
'msg_timeout'=>'Waktu tenggang habis. Apakah Anda ingin mencoba kembali?',
'msg_unknown_error_try_agan'=>'Terjadi kesalahan yang tidak diketahui. Apakah Anda ingin mencoba kembali?',
'msg_unknown'=>'Terjadi kesalahan yang tidak diketahui.',
'msg_not_close_saving_image'=>'Jangan menutup saat menyimpan gambar Anda!',
'msg_format_not_jpeg'=>'Lampiran ini bukan gambar JPEG.',
'msg_searching_no_result'=>'Pencarian tidak menemukan hasil. Silakan cari dengan kata kunci yang lain.',
'msg_username_not_registered'=>'Username yang Anda masukkan tidak terdaftar.',
'msg_fail_sending_email'=>'Server tidak dapat mengirimkan email. Silakan mencoba beberapa saat lagi.',
'msg_success_sending_email'=>'Link reset password telah dikirimkan ke alamat email Anda. Silakan buka email Anda.',
'msg_404'=>'Halaman yang Anda maksud tidak ditemukan. Pastikan bahwa Anda membuka URL yang benar atau kemungkinan halaman tersebut telah diubah sebelumnya atau profil anggota tersebut telah dihapus.',
'msg_403'=>'Anda tidak diperbolehkan emngakses halaman ini. Pastikan bahwa Anda membuka URL yang benar atau kemungkinan halaman tersebut telah diubah sebelumnya atau profil anggota tersebut telah dihapus.',
'msg_post_not_found'=>'Postingan yang Anda maksud tidak ditemukan. Pastikan bahwa Anda membuka URL yang benar atau kemungkinan postingan tersebut telah dihapus sebelumnya.',
'msg_password_not_match'=>'Password tidak sesuai.',
'msg_invalid_register_form'=>'Isian formulir pendaftaran salah.',
'msg_post_deleted'=>'Posting ini telah dihapus. Anda tidak bisa membalas postingan ini.',
'msg_shake'=>'Guncangkan handphone Anda dua kali lalu lihat apa yang terjadi.',
'msg_shake_not_supported'=>'Perangkat Anda tidak mendukung fitur ini.',
'msg_waiting_for_connection'=>'Menunggu sambungan...',

'btn_send_report'=>'Kirim Laporan',
'btn_save'=>'Simpan',
'btn_detect'=>'Deteksi',
'btn_share'=>'Bagikan',
'btn_login'=>'Login',
'btn_register'=>'Daftar',
'btn_reset_password'=>'Reset Password',
'btn_save_password'=>'Simpan Password',
'btn_send'=>'Kirim',
'btn_attach'=>'Lampirkan',
'btn_finish'=>'Selesai',
'btn_home'=>'Depan',
'btn_go'=>'Buka',
'btn_search'=>'Cari',
'btn_setting'=>'Pengaturan',
'btn_edit'=> 'Ubah',
'btn_yes'=> 'Ya',
'btn_no'=> 'Tidak',
'btn_ok'=> 'Lanjutkan',
'btn_cancel'=> 'Batalkan',
'btn_close'=> 'Tutup',
'btn_send'=> 'Kirim',
'btn_delete'=> 'Hapus',
'btn_delete_for_me'=> 'Hapus di Saya',
'btn_delete_for_all'=> 'Hapus di Semua',
'btn_clear_all'=> 'Clear All',

'txt_message_deleted'=>'Pesan telah dihapus',

'txt_clear_message'=>'Hapus Semua Pesan',
'txt_view_profile'=>'Lihat Profil',
'txt_block_user'=>'Blokir Pengguna',
'txt_minimize_chat_box'=>'Minimalkan Obrolan',
'txt_close_chat_box'=>'Tutup Obrolan',

'txt_find_more'=>'Cari Lagi',
'txt_select_option_state'=>'- Pilih Provinsi -',
'txt_select_option_city'=>'- Pilih Kota -',
'txt_add_option_state'=>'- Tambah Provinsi Baru -',
'txt_add_option_city'=>'- Tambah Kota Baru -',
'msg_confirm_change_input_type'=>'Apakah Anda yakin akan mengubah tipe input ini?',
'msg_confirm_delete_message'=>'Apakah Anda yakin akan menghapus pesan ini?',
'msg_confirm_clear_all_message'=>'Apakah Anda yakin akan menghapus semua pesan?',
'msg_confirm_clear_all_notification'=>'Apakah Anda yakin akan menghapus semua pemberitahuan?',
'msg_confirm_block_user'=>'Apakah Anda yakin akan memblokir pengguna ini?',

'txt_tooltip_post_send'=>'Kirim',
'txt_tooltip_post_add_image'=>'Tambah Gambar',
'txt_tooltip_post_add_image_360'=>'Tambah Panorama',
'txt_tooltip_post_add_location'=>'Tambah Lokasi',
'txt_tooltip_post_add_emoji'=>'Tambah Emoji',
'txt_tooltip_post_add_drawing'=>'Tambah Sketsa',

'txt_play_image_360'=>'Mainkan Panorama',
'txt_render_image_360'=>'Render Panorama',
'txt_embed_image_360'=>'Tanam Panorama',

'txt_find_image'=>'Cari Gambar',
'txt_ask_password'=>'Minta Password',
'txt_register'=>'Mendaftar',
'txt_reset_password'=>'Reset Password',
'txt_back'=>'Kembali',
'txt_backward'=> 'Mundur',
'txt_forward'=> 'Maju',
'txt_claim_id'=> 'Klaim ID',

'txt_delete_notif_read'=>'Hapus yang Telah Dibaca',
'txt_delete_notif_all'=>'Hapus Semua',
'txt_search_method'=>'Metode Pencarian',
'txt_search_people'=>'Pencarian Orang',
'txt_search_posts'=>'Pencarian Postingan',
'txt_search_what'=>'Mencari Apa',
'txt_find_what'=>'Mencari Apa',
'txt_location'=>'Lokasi',
'txt_your_location'=>'Lokasi Anda',
'txt_share_link'=>'Bagikan Link',
'txt_user_profile'=>'Profil Pengguna',
'txt_delete_message'=>'Hapus Pesan',
'txt_delete_chat'=>'Hapus Percakapan',


'txt_delete_for_me'=>'Hapus untuk saya',
'txt_delete_for_everyone'=>'Hapus untuk semua orang',


'txt_my_profile'=>'Profil Saya',
'txt_my_post'=>'Postingan Saya',
'txt_my_posts'=>'Postingan Saya',
'txt_my_image'=>'Gambar Saya',
'txt_my_images'=>'Gambar Saya',
'txt_my_panorama'=>'Panorama Saya',
'txt_my_panoramas'=>'Panorama Saya',
'txt_my_attachment'=>'Lampiran Saya',
'txt_my_attachments'=>'Lampiran Saya',
'txt_my_location' => 'Lokasi Saya',
'txt_my_locations'=>'Lokasi Saya',
'txt_about_me'=>'Tentang Saya',

'txt_applications'=>'Aplikasi',
'txt_games'=>'Permainan',
'txt_arti_nama'=>'Arti Nama',
'txt_planet_edu'=>'Planet Edu',
'txt_statistic'=>'Statistik',
'txt_logout'=>'Logout',
'txt_setting'=>'Pengaturan',
'txt_account_setting'=>'Pengaturan Akun',
'txt_application_setting'=>'Pengaturan Aplikasi',
'txt_background'=>'Latar',
'txt_avatar'=>'Avatar',


'txt_minute'=>'Menit',
'txt_hour'=>'Jam',
'txt_day'=>'Hari',
'txt_month'=>'Bulan',
'txt_year'=>'Tahun',

'txt_left_now'=>'Sekarang',
'txt_left_just_now'=>'Baru saja',
'txt_left_minute'=>'menit',
'txt_left_hour'=>'jam',
'txt_left_day'=>'hari',
'txt_left_month'=>'bulan',
'txt_left_year'=>'tahun',

'txt_left_now2'=>'Sekarang',
'txt_left_just_now2'=>'Baru saja',
'txt_left_minute2'=>'menit',
'txt_left_hour2'=>'jam',
'txt_left_day2'=>'hari',
'txt_left_month2'=>'bulan',
'txt_left_year2'=>'tahun',


'txt_fmt_say_something'=>'Katakan sesuatu kepada %s',
'txt_fmt_people_like'=> '%s Suka',
'txt_fmt_people_dislike'=> '%s Tidak suka',
'txt_fmt_about'=>'Tentang %s',
'txt_fmt_chat'=>'Ngobrol Dengan %s',
'txt_fmt_profile'=>'Profil %s',
'txt_fmt_posts'=>'Postingan %s',
'txt_fmt_images'=>'Gambar %s',
'txt_fmt_panorama'=>'Panorama %s',
'txt_fmt_avatar'=>'Avatar %s',
'txt_fmt_attachments'=>'Lampiran %s',
'txt_fmt_locations'=>'Lokasi %s',
'txt_fmt_about'=>'Tentang %s',
'txt_fmt_referal'=>'Referal %s',

'txt_fmt_report_member'=>'Laporkan %s',

'txt_search_for'=>'Mencari',
'txt_age_from'=>'Usia Minimum',
'txt_age_to'=>'Usia Maksimum',
'txt_seraching_radius'=>'Radius Pencarian',
'txt_last_activity'=>'Aktivitas Terakhir',

'txt_video_call'=>'Panggilan Video',
'txt_voice_call'=>'Panggilan Suara',
'txt_accept'=>'Terima',
'txt_reject'=>'Tolak',
'txt_make_call'=>'Panggil',
'txt_call'=>'Panggil',
'txt_end_call'=>'Akhiri Panggilan',
'txt_you_call'=>'Anda Memanggil %s',
'txt_call_you'=>'%s Memanggil Anda',


'txt_gender_all'=>'Semua',
'txt_gender_man_only'=>'Laki-Laki Saja',
'txt_gender_woman_only'=>'Perempuan Saja',

'txt_no_post'=>'Anda belum memposting apapun di Planetbiru. Planetbiru adalah media sosial asli Indonesia. Di sini Anda dapat menemukan teman baru. Aktifkan radar dan temukan orang di sekitar Anda.'

);



?>