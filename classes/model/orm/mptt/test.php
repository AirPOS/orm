<?

class Model_ORM_MPTT_Test extends ORM_MPTT
{
	/*
	 * Test Table Name
	 */
	private static $_test_table_name = "orm_mptt_tests";
	
	public static function create_table()
	{
		self::delete_table();
		Database::instance(Kohana::$environment)->query
		(
			NULL,
			"CREATE TABLE `orm_mptt_tests` (
			  `id` char(36) COLLATE utf8_unicode_ci NOT NULL,
			  `level_id` int(11) NOT NULL,
			  `left_id` int(11) NOT NULL,
			  `right_id` int(11) NOT NULL,
			  `scope_id` int(11) NOT NULL,
			  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
			TRUE
		);
		self::reset_table();
	}
	
	public static function reset_table()
	{
		Database::instance(Kohana::$environment)->query(NULL, 'TRUNCATE TABLE `'.self::$_test_table_name.'`', TRUE);
		DB::insert(self::$_test_table_name)->values(array('id' => "090be1b2-ca9a-440f-bb15-5f86da4dac51" /*1*/, 'level_id' => 0,'left_id' => 1, 'right_id' => 22, 'scope_id' => 1, 'name' => 'Root Node'))->execute(Kohana::$environment);

		DB::insert(self::$_test_table_name)->values(array('id' => "1cdc11cb-06b4-45eb-a7a3-7f869a0c3c57" /*2*/, 'level_id' => 1,'left_id' => 2, 'right_id' => 3, 'scope_id' => 1, 'name' => 'Leaf Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "32732ce0-6d38-42e2-af7a-166389ab6a19" /*3*/, 'level_id' => 1,'left_id' => 4, 'right_id' => 7, 'scope_id' => 1, 'name' => 'Normal Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "b0c6b763-eb14-4de9-b398-84ae0b6fdbd9" /*4*/, 'level_id' => 2,'left_id' => 5, 'right_id' => 6, 'scope_id' => 1, 'name' => 'Leaf Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "e852e66b-5183-4091-8bb8-3087d96bd3c2" /*5*/, 'level_id' => 1,'left_id' => 8, 'right_id' => 9, 'scope_id' => 1, 'name' => 'Leaf Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "bfc12e49-70f9-4595-b8e6-dbcbfcf002bc" /*6*/, 'level_id' => 1,'left_id' => 10, 'right_id' => 21, 'scope_id' => 1, 'name' => 'Normal Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "1631fd85-3317-4a57-8892-a033d84de948" /*7*/, 'level_id' => 2,'left_id' => 11, 'right_id' => 12, 'scope_id' => 1, 'name' => 'Leaf Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "a29042a1-be9e-4c94-aaf0-694a7d1dd323" /*8*/, 'level_id' => 2,'left_id' => 13, 'right_id' => 18, 'scope_id' => 1, 'name' => 'Normal Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "5c012ed1-2697-416f-9d4f-9a58e86a0c2a" /*9*/, 'level_id' => 3,'left_id' => 14, 'right_id' => 15, 'scope_id' => 1, 'name' => 'Leaf Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "ed23c1d0-afd0-4661-b677-561066e847a0" /*10*/, 'level_id' => 3,'left_id' => 16, 'right_id' => 17, 'scope_id' => 1, 'name' => 'Leaf Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "4ae0c712-68e1-46b2-9bb0-d35860e56d8f" /*11*/, 'level_id' => 2,'left_id' => 19, 'right_id' => 20, 'scope_id' => 1, 'name' => 'Leaf Node'))->execute(Kohana::$environment);
		
		DB::insert(self::$_test_table_name)->values(array('id' => "35b68b0f-bc2b-45c5-901a-31217e5fad61" /*12*/, 'level_id' => 0,'left_id' => 1, 'right_id' => 22, 'scope_id' => 2, 'name' => 'Root Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "9a5bc64f-53f6-4fc6-8aea-817b6b6c9e89" /*13*/, 'level_id' => 1,'left_id' => 2, 'right_id' => 3, 'scope_id' => 2, 'name' => 'Leaf Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "868b9eda-6607-462e-8957-7c84477aa1ce" /*14*/, 'level_id' => 1,'left_id' => 4, 'right_id' => 7, 'scope_id' => 2, 'name' => 'Normal Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "89a53ef0-f780-4fa0-843f-6e88535bb444" /*15*/, 'level_id' => 2,'left_id' => 5, 'right_id' => 6, 'scope_id' => 2, 'name' => 'Leaf Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "ec3bb4b5-cbca-4607-8f16-bc2752f3bf7a" /*16*/, 'level_id' => 1,'left_id' => 8, 'right_id' => 9, 'scope_id' => 2, 'name' => 'Leaf Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "795f927c-fd9a-4d39-8229-fa0293c4af96" /*17*/, 'level_id' => 1,'left_id' => 10, 'right_id' => 21, 'scope_id' => 2, 'name' => 'Normal Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "96004bac-2511-4014-bb9f-c55d50de14fb" /*18*/, 'level_id' => 2,'left_id' => 11, 'right_id' => 12, 'scope_id' => 2, 'name' => 'Leaf Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "90324423-fe82-4e44-9a4c-cb4739ad8f34" /*19*/, 'level_id' => 2,'left_id' => 13, 'right_id' => 18, 'scope_id' => 2, 'name' => 'Normal Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "358f132f-0073-43c7-a479-5094b3d4f84a" /*20*/, 'level_id' => 3,'left_id' => 14, 'right_id' => 15, 'scope_id' => 2, 'name' => 'Leaf Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "f226ce07-a632-4939-91f4-8c9fa0b22241" /*21*/, 'level_id' => 3,'left_id' => 16, 'right_id' => 17, 'scope_id' => 2, 'name' => 'Leaf Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "22e9b191-d4c7-42f4-ac30-429fcb481aed" /*22*/, 'level_id' => 2,'left_id' => 19, 'right_id' => 20, 'scope_id' => 2, 'name' => 'Leaf Node'))->execute(Kohana::$environment);
		
		DB::insert(self::$_test_table_name)->values(array('id' => "0e79e954-0692-4751-8938-266e27293083" /*23*/, 'level_id' => 0,'left_id' => 1, 'right_id' => 22, 'scope_id' => 3, 'name' => 'Root Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "1cc6223b-0231-404c-b864-6575b8807c11" /*24*/, 'level_id' => 1,'left_id' => 2, 'right_id' => 3, 'scope_id' => 3, 'name' => 'Leaf Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "12255c2a-8e28-4e25-8771-8f696ee124b0" /*25*/, 'level_id' => 1,'left_id' => 4, 'right_id' => 7, 'scope_id' => 3, 'name' => 'Normal Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "2e708c0d-2347-460d-9aac-e4962f20fcbb" /*26*/, 'level_id' => 2,'left_id' => 5, 'right_id' => 6, 'scope_id' => 3, 'name' => 'Leaf Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "67ed155f-f64f-42fd-aa63-6ef64a465d43" /*27*/, 'level_id' => 1,'left_id' => 8, 'right_id' => 9, 'scope_id' => 3, 'name' => 'Leaf Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "e863555e-f829-4b3a-b32a-5e3e1b87764a" /*28*/, 'level_id' => 1,'left_id' => 10, 'right_id' => 21, 'scope_id' => 3, 'name' => 'Normal Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "f6d4c417-bffd-4dde-b03e-f969a11f8b8a" /*29*/, 'level_id' => 2,'left_id' => 11, 'right_id' => 12, 'scope_id' => 3, 'name' => 'Leaf Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "0d033d43-c192-41a9-abd3-6dfad9bdbe54" /*30*/, 'level_id' => 2,'left_id' => 13, 'right_id' => 18, 'scope_id' => 3, 'name' => 'Normal Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "f19c81d7-1357-4a91-ab08-8c05845c9c1c" /*31*/, 'level_id' => 3,'left_id' => 14, 'right_id' => 15, 'scope_id' => 3, 'name' => 'Leaf Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "ed54b945-0344-496b-bb1b-6a07892d8c71" /*32*/, 'level_id' => 3,'left_id' => 16, 'right_id' => 17, 'scope_id' => 3, 'name' => 'Leaf Node'))->execute(Kohana::$environment);
		DB::insert(self::$_test_table_name)->values(array('id' => "d2de98e4-3bc1-4654-814b-e38edffb7b89" /*33*/, 'level_id' => 2,'left_id' => 19, 'right_id' => 20, 'scope_id' => 3, 'name' => 'Leaf Node'))->execute(Kohana::$environment);
	}
	
	public static function delete_table()
	{
		Database::instance(Kohana::$environment)->query(NULL, 'DROP TABLE IF EXISTS `'.self::$_test_table_name.'`', TRUE);
	}
}