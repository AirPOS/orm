<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * ORM MPTT Unit Test
 *
 * @author Kieran Graham
 */
class ORM_MPTT_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * Set Up
	 */
	public function setUp()
	{
		Model_ORM_MPTT_Test::create_table();
	}
	
	/**
	 * Tear Down
	 */
	public function tearDown()
	{
		Model_ORM_MPTT_Test::delete_table();
	}
	
	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_insert_as_new_root()
	{
		$model = ORM::factory('orm_mptt_test');
		$model->name = "Test Root Node";
		
		// Scope 1 should already exist
		$this->assertFalse($model->insert_as_new_root(1));
		
		// This should create scope 4 and return iteself
		$this->assertEquals($model->insert_as_new_root(4), $model);
		
		// Check new root has been give the correct MPTT values
		$this->assertEquals($model->left_id, 1);
		$this->assertEquals($model->right_id, 2);
		$this->assertEquals($model->level_id, 0);
		$this->assertEquals($model->scope_id, 4);
		
		// Make sure we haven't invalidated the tree
		// $this->assertTrue($model->verify_tree());
	}
	
	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_load()
	{
		$model = ORM::factory('orm_mptt_test', "32732ce0-6d38-42e2-af7a-166389ab6a19");
		
		$this->assertTrue($model->loaded());
		$this->assertEquals($model->left_id, 4);
		$this->assertEquals($model->right_id, 7);
	}
	
	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_root()
	{
		$root = ORM::factory('orm_mptt_test', "090be1b2-ca9a-440f-bb15-5f86da4dac51");
		$node = ORM::factory('orm_mptt_test', "a29042a1-be9e-4c94-aaf0-694a7d1dd323");
		
		$this->assertTrue($root->loaded());
		$this->assertEquals($node->root()->id, $root->id);
	}
	
	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_has_children()
	{
		$node_with_one_child = ORM::factory('orm_mptt_test', "32732ce0-6d38-42e2-af7a-166389ab6a19");
		$node_with_children = ORM::factory('orm_mptt_test', "a29042a1-be9e-4c94-aaf0-694a7d1dd323");
		$leaf_node = ORM::factory('orm_mptt_test', "e852e66b-5183-4091-8bb8-3087d96bd3c2");
		
		$this->assertTrue($node_with_one_child->has_children());
		$this->assertFalse($node_with_one_child->is_leaf());
		
		$this->assertTrue($node_with_children->has_children());
		$this->assertFalse($node_with_children->is_leaf());
		
		$this->assertFalse($leaf_node->has_children());
		$this->assertTrue($leaf_node->is_leaf());
	}
	
	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_is_descendant()
	{
		$node_3 = ORM::factory('orm_mptt_test', "32732ce0-6d38-42e2-af7a-166389ab6a19");
		$node_4 = ORM::factory('orm_mptt_test', "b0c6b763-eb14-4de9-b398-84ae0b6fdbd9");
		$root_node = $node_3->root();
		
		$this->assertTrue($node_3->is_descendant($root_node));
		$this->assertTrue($node_4->is_descendant($root_node));
		$this->assertTrue($node_4->is_descendant($node_3));
		$this->assertFalse($node_3->is_descendant($node_3));
		$this->assertFalse($node_3->is_descendant($node_4));
	}
	
	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_is_child()
	{
		$node_3 = ORM::factory('orm_mptt_test', "32732ce0-6d38-42e2-af7a-166389ab6a19");
		$node_4 = ORM::factory('orm_mptt_test', "b0c6b763-eb14-4de9-b398-84ae0b6fdbd9");
		$node_5 = ORM::factory('orm_mptt_test', "e852e66b-5183-4091-8bb8-3087d96bd3c2");
		$root_node = $node_3->root();
		
		$this->assertTrue($node_4->is_child($node_3));
		$this->assertTrue($node_3->is_child($root_node));
		$this->assertFalse($node_4->is_child($root_node));
		$this->assertFalse($node_4->is_child($node_5));
		$this->assertFalse($node_4->is_child($node_4));
	}
	
	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_is_parent()
	{
		$node_3 = ORM::factory('orm_mptt_test', "32732ce0-6d38-42e2-af7a-166389ab6a19");
		$node_4 = ORM::factory('orm_mptt_test', "b0c6b763-eb14-4de9-b398-84ae0b6fdbd9");
		$node_5 = ORM::factory('orm_mptt_test', "e852e66b-5183-4091-8bb8-3087d96bd3c2");
		$root_node = $node_3->root();
		
		$this->assertTrue($node_3->is_parent($node_4));
		$this->assertTrue($root_node->is_parent($node_3));
		$this->assertFalse($root_node->is_parent($node_4));
		$this->assertFalse($node_4->is_parent($node_5));
		$this->assertFalse($node_4->is_parent($node_4));
	}
	
	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_is_sibling()
	{
		$node_5 = ORM::factory('orm_mptt_test', "e852e66b-5183-4091-8bb8-3087d96bd3c2");
		$node_6 = ORM::factory('orm_mptt_test', "bfc12e49-70f9-4595-b8e6-dbcbfcf002bc");
		$node_7 = ORM::factory('orm_mptt_test', "1631fd85-3317-4a57-8892-a033d84de948");
		
		$this->assertTrue($node_5->is_sibling($node_6));
		$this->assertFalse($node_5->is_sibling($node_7));
		$this->assertFalse($node_5->is_sibling($node_5));
	}
	
	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_is_root()
	{
		$node_5 = ORM::factory('orm_mptt_test', "e852e66b-5183-4091-8bb8-3087d96bd3c2");
		$root = $node_5->root();
		
		$this->assertTrue($root->is_root());
		$this->assertFalse($node_5->is_root());
	}
	
	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_parent()
	{
		$node_5 = ORM::factory('orm_mptt_test', "e852e66b-5183-4091-8bb8-3087d96bd3c2");
		$root = $node_5->root();
		
		$this->assertEquals($node_5->parent(), $root);
		$this->assertFalse($root->parent()->loaded());
	}
	
	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_parents()
	{
		$node_5 = ORM::factory('orm_mptt_test', "e852e66b-5183-4091-8bb8-3087d96bd3c2");
		$node_9 = ORM::factory('orm_mptt_test', "5c012ed1-2697-416f-9d4f-9a58e86a0c2a");
		
		$node_5_parents = $node_5->parents()->find_all();
		$node_9_parents = $node_9->parents()->find_all();
		$node_9_parents_desc = $node_9->parents(TRUE, 'DESC')->find_all();
		
		$this->assertSame(get_class($node_5_parents), 'Database_MySQL_Result');
		$this->assertSame(count($node_5_parents), 1);
		$this->assertSame(count($node_5->parents(FALSE)->find_all()), 0);
		$this->assertEquals($node_5_parents[0]->id, "090be1b2-ca9a-440f-bb15-5f86da4dac51");
		
		$this->assertSame(get_class($node_9_parents), 'Database_MySQL_Result');
		$this->assertSame(count($node_9_parents), 3);
		$this->assertSame(count($node_9->parents(FALSE)->find_all()), 2);
		
		$this->assertEquals($node_9_parents[0]->id, "090be1b2-ca9a-440f-bb15-5f86da4dac51");
		$this->assertEquals($node_9_parents[1]->id, "bfc12e49-70f9-4595-b8e6-dbcbfcf002bc");
		$this->assertEquals($node_9_parents[2]->id, "a29042a1-be9e-4c94-aaf0-694a7d1dd323");
		
		$this->assertEquals($node_9_parents_desc[2]->id, "090be1b2-ca9a-440f-bb15-5f86da4dac51");
		$this->assertEquals($node_9_parents_desc[1]->id, "bfc12e49-70f9-4595-b8e6-dbcbfcf002bc");
		$this->assertEquals($node_9_parents_desc[0]->id, "a29042a1-be9e-4c94-aaf0-694a7d1dd323");
	}
	
	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_children()
	{
		$node_6 = ORM::factory('orm_mptt_test', "bfc12e49-70f9-4595-b8e6-dbcbfcf002bc");

		$node_6_children = $node_6->children()->find_all();
		$node_6_children_desc = $node_6->children(FALSE, 'DESC')->find_all();
		
		$this->assertSame(get_class($node_6_children), 'Database_MySQL_Result');
		$this->assertEquals(count($node_6_children), 3);
		$this->assertEquals(count($node_6->children(TRUE)->find_all()), 4);
		$this->assertEquals($node_6_children[0]->id, "1631fd85-3317-4a57-8892-a033d84de948");
		$this->assertEquals($node_6_children[1]->id, "a29042a1-be9e-4c94-aaf0-694a7d1dd323");
		$this->assertEquals($node_6_children[2]->id, "4ae0c712-68e1-46b2-9bb0-d35860e56d8f");
		
		$this->assertEquals($node_6_children_desc[0]->id, "4ae0c712-68e1-46b2-9bb0-d35860e56d8f");
		$this->assertEquals($node_6_children_desc[1]->id, "a29042a1-be9e-4c94-aaf0-694a7d1dd323");
		$this->assertEquals($node_6_children_desc[2]->id, "1631fd85-3317-4a57-8892-a033d84de948");
	}
	
	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_descendants()
	{
		$node_6 = ORM::factory('orm_mptt_test', "bfc12e49-70f9-4595-b8e6-dbcbfcf002bc");

		$node_6_descendants = $node_6->descendants()->find_all();
		
		$this->assertSame(get_class($node_6_descendants), 'Database_MySQL_Result');
		$this->assertEquals(count($node_6_descendants), 5);
		$this->assertEquals(count($node_6->descendants(TRUE)->find_all()), 6);
		$this->assertEquals($node_6_descendants[0]->id, "1631fd85-3317-4a57-8892-a033d84de948");
		$this->assertEquals($node_6_descendants[1]->id, "a29042a1-be9e-4c94-aaf0-694a7d1dd323");
		$this->assertEquals($node_6_descendants[2]->id, "5c012ed1-2697-416f-9d4f-9a58e86a0c2a");
		$this->assertEquals($node_6_descendants[3]->id, "ed23c1d0-afd0-4661-b677-561066e847a0");
		$this->assertEquals($node_6_descendants[4]->id, "4ae0c712-68e1-46b2-9bb0-d35860e56d8f");
	}
	
	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_siblings()
	{	
		$node_5 = ORM::factory('orm_mptt_test', "e852e66b-5183-4091-8bb8-3087d96bd3c2");

		$node_5_siblings = $node_5->siblings()->find_all();
		
		$this->assertSame(get_class($node_5_siblings), 'Database_MySQL_Result');
		$this->assertEquals(count($node_5_siblings), 3);
		$this->assertEquals(count($node_5->siblings(TRUE)->find_all()), 4);
		$this->assertEquals($node_5_siblings[0]->id, "1cdc11cb-06b4-45eb-a7a3-7f869a0c3c57");
		$this->assertEquals($node_5_siblings[1]->id, "32732ce0-6d38-42e2-af7a-166389ab6a19");
		$this->assertEquals($node_5_siblings[2]->id, "bfc12e49-70f9-4595-b8e6-dbcbfcf002bc");
	}
	
	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_leaves()
	{
		$node_1 = ORM::factory('orm_mptt_test', "090be1b2-ca9a-440f-bb15-5f86da4dac51");

		$node_1_leaves = $node_1->leaves()->find_all();
		
		$this->assertSame(get_class($node_1_leaves), 'Database_MySQL_Result');
		$this->assertEquals(count($node_1_leaves), 2);
		$this->assertEquals($node_1_leaves[0]->id, "1cdc11cb-06b4-45eb-a7a3-7f869a0c3c57");
		$this->assertEquals($node_1_leaves[1]->id, "e852e66b-5183-4091-8bb8-3087d96bd3c2");
	}

	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_insert_as_first_child()
	{
		$new = ORM::factory('orm_mptt_test');
		$new->name = 'Test Element';
		
		$node_3 = ORM::factory('orm_mptt_test', "32732ce0-6d38-42e2-af7a-166389ab6a19");
		
		$this->assertFalse($node_3->insert_as_first_child("b0c6b763-eb14-4de9-b398-84ae0b6fdbd9"));
		$this->assertEquals($new->insert_as_first_child($node_3), $new);
			
		// Reload node 3 to check insert worked
		$node_3 = ORM::factory('orm_mptt_test', "32732ce0-6d38-42e2-af7a-166389ab6a19");
		$node_3_children = $node_3->children()->find_all();
		
		$this->assertSame(count($node_3_children), 2);
		$this->assertEquals($node_3_children[0]->id, $new->id);
		// $this->assertTrue($node_3->verify_tree());
	}

	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_insert_as_last_child()
	{
		$new = ORM::factory('orm_mptt_test');
		$new->name = 'Test Element';
		
		$node_3 = ORM::factory('orm_mptt_test', "32732ce0-6d38-42e2-af7a-166389ab6a19");
		
		$this->assertFalse($node_3->insert_as_last_child("b0c6b763-eb14-4de9-b398-84ae0b6fdbd9"));
		$this->assertEquals($new->insert_as_last_child($node_3), $new);
			
		// Reload node 3 to check insert worked
		$node_3 = ORM::factory('orm_mptt_test', "32732ce0-6d38-42e2-af7a-166389ab6a19");
		$node_3_children = $node_3->children()->find_all();
		
		$this->assertSame(count($node_3_children), 2);
		$this->assertEquals($node_3_children[1]->id, $new->id);
		// $this->assertTrue($node_3->verify_tree());
	}

	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_insert_as_prev_sibling()
	{
		$new = ORM::factory('orm_mptt_test');
		$new->name = 'Test Element';
		
		$node_3 = ORM::factory('orm_mptt_test', "32732ce0-6d38-42e2-af7a-166389ab6a19");
		
		$this->assertFalse($node_3->insert_as_prev_sibling("b0c6b763-eb14-4de9-b398-84ae0b6fdbd9"));
		$this->assertEquals($new->insert_as_prev_sibling($node_3), $new);
			
		// Reload node 3 to check insert worked
		$node_3 = ORM::factory('orm_mptt_test', "32732ce0-6d38-42e2-af7a-166389ab6a19");
		$node_3_siblings = $node_3->siblings(TRUE)->find_all();
		
		$this->assertSame(count($node_3_siblings), 5);
		$this->assertEquals($node_3_siblings[1]->id, $new->id);
		// $this->assertTrue($node_3->verify_tree());
	}

	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_insert_as_next_sibling()
	{
		$new = ORM::factory('orm_mptt_test');
		$new->name = 'Test Element';
		
		$node_3 = ORM::factory('orm_mptt_test', "32732ce0-6d38-42e2-af7a-166389ab6a19");
		
		$this->assertFalse($node_3->insert_as_next_sibling("b0c6b763-eb14-4de9-b398-84ae0b6fdbd9"));
		$this->assertEquals($new->insert_as_next_sibling($node_3), $new);
			
		// Reload node 3 to check insert worked
		$node_3 = ORM::factory('orm_mptt_test', "32732ce0-6d38-42e2-af7a-166389ab6a19");
		$node_3_siblings = $node_3->siblings(TRUE)->find_all();
		
		$this->assertSame(count($node_3_siblings), 5);
		$this->assertEquals($node_3_siblings[2]->id, $new->id);
		// $this->assertTrue($node_3->verify_tree());
	}

	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_delete()
	{
		$node_3 = ORM::factory('orm_mptt_test', "32732ce0-6d38-42e2-af7a-166389ab6a19");
		$node_3->delete();
		
		$root = $node_3->root();
		$root_children = $root->children()->find_all();
		
		$this->assertSame(count($root_children), 3);
		$this->assertNotEquals($root_children[1]->id, "32732ce0-6d38-42e2-af7a-166389ab6a19");
		// $this->assertTrue($node_3->verify_tree());
	}

	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_move_to_first_child()
	{
		$node_3 = ORM::factory('orm_mptt_test', "32732ce0-6d38-42e2-af7a-166389ab6a19");
		
		$this->assertFalse($node_3->move_to_first_child("32732ce0-6d38-42e2-af7a-166389ab6a19"));
		$this->assertEquals($node_3->move_to_first_child("bfc12e49-70f9-4595-b8e6-dbcbfcf002bc"), $node_3);
		
		// Load node 6 to check move worked
		$node_6 = ORM::factory('orm_mptt_test', "bfc12e49-70f9-4595-b8e6-dbcbfcf002bc");
		$node_6_children = $node_6->children()->find_all();
		$root = $node_6->root();
		$root_children = $root->children()->find_all();
		
		$this->assertEquals(count($node_6_children), 4);
		$this->assertEquals($node_6_children[0]->id, "32732ce0-6d38-42e2-af7a-166389ab6a19");
		$this->assertEquals(count($root_children), 3);
		$this->assertNotEquals($root_children[1]->id, "32732ce0-6d38-42e2-af7a-166389ab6a19");
		// $this->assertTrue($node_3->verify_tree());
	}

	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_move_to_last_child()
	{
		$node_3 = ORM::factory('orm_mptt_test', "32732ce0-6d38-42e2-af7a-166389ab6a19");
		
		$this->assertFalse($node_3->move_to_last_child("32732ce0-6d38-42e2-af7a-166389ab6a19"));
		$this->assertEquals($node_3->move_to_last_child("bfc12e49-70f9-4595-b8e6-dbcbfcf002bc"), $node_3);
		
		// Load node 6 to check move worked
		$node_6 = ORM::factory('orm_mptt_test', "bfc12e49-70f9-4595-b8e6-dbcbfcf002bc");
		$node_6_children = $node_6->children()->find_all();
		$root = $node_6->root();
		$root_children = $root->children()->find_all();

		$this->assertSame(count($node_6_children), 4);
		$this->assertEquals($node_6_children[3]->id, "32732ce0-6d38-42e2-af7a-166389ab6a19");
		$this->assertSame(count($root_children), 3);
		$this->assertNotEquals($root_children[1]->id, "32732ce0-6d38-42e2-af7a-166389ab6a19");
		// $this->assertTrue($node_3->verify_tree());
	}

	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_move_to_prev_sibling()
	{
		$node_3 = ORM::factory('orm_mptt_test', "32732ce0-6d38-42e2-af7a-166389ab6a19");
		
		$this->assertFalse($node_3->move_to_prev_sibling("32732ce0-6d38-42e2-af7a-166389ab6a19"));
		$this->assertEquals($node_3->move_to_prev_sibling("a29042a1-be9e-4c94-aaf0-694a7d1dd323"), $node_3);
		
		// Load node 8 to check move worked
		$node_8 = ORM::factory('orm_mptt_test', "a29042a1-be9e-4c94-aaf0-694a7d1dd323");
		$node_8_siblings = $node_8->siblings(TRUE)->find_all();
		$root = $node_8->root();
		$root_children = $root->children()->find_all();

		$this->assertSame(count($node_8_siblings), 4);
		$this->assertEquals($node_8_siblings[1]->id, "32732ce0-6d38-42e2-af7a-166389ab6a19");
		$this->assertSame(count($root_children), 3);
		$this->assertNotEquals($root_children[1]->id, "32732ce0-6d38-42e2-af7a-166389ab6a19");
		// $this->assertTrue($node_3->verify_tree());
	}
	
	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_move_to_next_sibling()
	{
		$node_3 = ORM::factory('orm_mptt_test', "32732ce0-6d38-42e2-af7a-166389ab6a19");
		
		$this->assertFalse($node_3->move_to_next_sibling("32732ce0-6d38-42e2-af7a-166389ab6a19"));
		$this->assertEquals($node_3->move_to_next_sibling("a29042a1-be9e-4c94-aaf0-694a7d1dd323"), $node_3);
		
		// Load node 8 to check move worked
		$node_8 = ORM::factory('orm_mptt_test', "a29042a1-be9e-4c94-aaf0-694a7d1dd323");
		$node_8_siblings = $node_8->siblings(TRUE)->find_all();
		$root = $node_8->root();
		$root_children = $root->children()->find_all();

		$this->assertSame(count($node_8_siblings), 4);
		$this->assertEquals($node_8_siblings[2]->id, "32732ce0-6d38-42e2-af7a-166389ab6a19");
		$this->assertSame(count($root_children), 3);
		$this->assertNotEquals($root_children[1]->id, "32732ce0-6d38-42e2-af7a-166389ab6a19");
		// $this->assertTrue($node_3->verify_tree());
	}
	
	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_first_child()
	{
		$node_6 = ORM::factory('orm_mptt_test', "bfc12e49-70f9-4595-b8e6-dbcbfcf002bc");
		$first_child = $node_6->first_child->find();
		
		$this->assertEquals($first_child->id, "1631fd85-3317-4a57-8892-a033d84de948");
	}

	/**
	 * @test
	 * @group orm.mptt
	 */
	public function test_last_child()
	{
		$node_6 = ORM::factory('orm_mptt_test', "bfc12e49-70f9-4595-b8e6-dbcbfcf002bc");
		$last_child = $node_6->last_child->find();
		
		$this->assertEquals($last_child->id, "4ae0c712-68e1-46b2-9bb0-d35860e56d8f");
	}
}