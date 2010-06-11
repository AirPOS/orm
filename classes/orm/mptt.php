<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Modified Preorder Tree Traversal Class.
 * 
 * Ported from Sprig_MPTT originally by Matthew Davies, Kiall Mac Innes and Paul Banks
 */
abstract class ORM_MPTT extends ORM
{
	/**
	 * @access public
	 * @var string left column name.
	 */
	public $_left_column = 'left_id';
	
	/**
	 * @access public
	 * @var string right column name.
	 */
	public $_right_column = 'right_id';
	
	/**
	 * @access public
	 * @var string level column name.
	 */
	public $_level_column = 'level_id';
	
	/**
	 * @access public
	 * @var string scope column name.
	 **/
	public $_scope_column = 'scope_id';
	
	/**
	 * Locks table.
	 *
	 * @access private
	 */
	protected function lock()
	{
		Database::instance((string) $this->_db)->query(NULL, 'LOCK TABLE '.$this->_table_name.' WRITE', TRUE);
	}
	
	/**
	 * Unlock table.
	 *
	 * @access private
	 */
	protected function unlock()
	{
		Database::instance((string) $this->_db)->query(NULL, 'UNLOCK TABLES', TRUE);
	}

	/**
	 * Does the current node have children?
	 *
	 * @access public
	 * @return bool
	 */
	public function has_children()
	{
		return (($this->{$this->_right_column} - $this->{$this->_left_column}) > 1);
	}
	
	/**
	 * Is the current node a leaf node?
	 *
	 * @access public
	 * @return bool
	 */
	public function is_leaf()
	{
		return ! $this->has_children();
	}
	
	/**
	 * Is the current node a descendant of the supplied node.
	 *
	 * @access public
	 * @param ORM_MPTT $target Target
	 * @return bool
	 */
	public function is_descendant($target)
	{
		return (
					$this->{$this->_left_column} > $target->{$this->_left_column} 
					AND $this->{$this->_right_column} < $target->{$this->_right_column} 
					AND $this->{$this->_scope_column} = $target->{$this->_scope_column}
				);
	}
	
	/**
	 * Is the current node a direct child of the supplied node?
	 *
	 * @access public
	 * @param ORM_MPTT $target Target
	 * @return bool
	 */
	public function is_child($target)
	{
		return ($this->parent->{$this->_primary_key} === $target->{$this->_primary_key});
	}
	
	/**
	 * Is the current node the direct parent of the supplied node?
	 *
	 * @access public
	 * @param ORM_MPTT $target Target
	 * @return bool
	 */
	public function is_parent($target)
	{
		return ($this->{$this->_primary_key} === $target->parent->{$this->_primary_key});
	}
	
	/**
	 * Is the current node a sibling of the supplied node
	 *
	 * @access public
	 * @param ORM_MPTT $target Target
	 * @return bool
	 */
	public function is_sibling($target)
	{
		if ($this->{$this->_primary_key} === $target->{$this->_primary_key})
			return FALSE;
		
		return ($this->parent->{$this->_primary_key} === $target->parent->{$this->_primary_key});
	}
	
	/**
	 * Is the current node a root node?
	 *
	 * @access public
	 * @return bool
	 */
	public function is_root()
	{
		return ($this->{$this->_left_column} == 1);
	}
	
	/**
	 * Returns the root node.
	 *
	 * @access protected
	 * @return ORM_MPTT/FALSE on invalid scope
	 */
	public function root($scope = NULL)
	{
		$this->_load();
		
		if ($scope === NULL AND $this->_loaded)
		{
			$scope = $this->{$this->_scope_column};
		}
		elseif ($scope === NULL AND ! $this->_loaded)
		{
			return FALSE;
		}
		
		return ORM_MPTT::factory($this->_object_name)->where($this->_left_column, '=', 1)->where($this->_scope_column, '=', $scope)->find();
	}
	
	/**
	 * Returns the parent of the current node.
	 *
	 * @access public
	 * @return ORM_MPTT
	 */
	public function parent()
	{
		return $this->parents(TRUE, 'ASC', TRUE)->find();
	}
	
	/**
	 * Returns the parents of the current node.
	 *
	 * @access public
	 * @param bool $root include the root node?
	 * @param string $direction direction to order the left column by.
	 * @return OTM_MPTT
	 */
	public function parents($root = TRUE, $direction = 'ASC', $direct_parent_only = FALSE)
	{
		$parents = ORM_MPTT::factory($this->_object_name)
							->where($this->_left_column, '<=', $this->{$this->_left_column})
							->where($this->_right_column, '>=', $this->{$this->_right_column})
							->where($this->_primary_key, '<>', $this->{$this->_primary_key})
							->where($this->_scope_column, '=', $this->{$this->_scope_column})
							->order_by($this->_left_column, $direction);
			
		if ( ! $root)
		{
			$parents->where($this->_left_column, '!=', 1);
		}	
		
		$limit = FALSE;
		
		if ($direct_parent_only)
		{
			$parents->where($this->_level_column, '=', $this->{$this->_level_column} - 1)
					->limit(1);
		}
		
		return $parents;
	}
	
	/**
	 * Returns the children of the current node.
	 *
	 * @access public
	 * @param bool $self include the current loaded node?
	 * @param string $direction direction to order the left column by.
	 * @return ORM_MPTT
	 */
	public function children($self = FALSE, $direction = 'ASC', $limit = FALSE)
	{
		return $this->descendants($self, $direction, TRUE, FALSE, $limit);
	}
	
	/**
	 * Returns the descendants of the current node.
	 *
	 * @access public
	 * @param bool $self include the current loaded node?
	 * @param string $direction direction to order the left column by.
	 * @return ORM_MPTT
	 */
	public function descendants($self = FALSE, $direction = 'ASC', $direct_children_only = FALSE, $leaves_only = FALSE, $limit = FALSE)
	{
		$left_operator = $self ? '>=' : '>';
		$right_operator = $self ? '<=' : '<';
		
		$descendants = ORM_MPTT::factory($this->_object_name)
			->where($this->_left_column, $left_operator, $this->{$this->_left_column})
			->where($this->_right_column, $right_operator, $this->{$this->_right_column})
			->where($this->_scope_column, '=', $this->{$this->_scope_column})
			->order_by($this->_left_column, $direction);
		
		if ($direct_children_only)
		{
			if ($self)
			{
				$descendants
					->and_where_open()
					->where($this->_level_column, '=', $this->{$this->_level_column})
					->or_where($this->_level_column, '=', $this->{$this->_level_column} + 1)
					->and_where_close();
			}
			else
			{
				$descendants->where($this->_level_column, '=', $this->{$this->_level_column} + 1);
			}
		}
		
		if ($leaves_only)
		{
			$descendants->where($this->_right_column, '=', new Database_Expression('`'.$this->_left_column.'` + 1'));
		}
		
		if ($limit)
		{
			$descendants->limit($limit);
		}
		
		return $descendants;
	}
	
	/**
	 * Returns the siblings of the current node
	 *
	 * @access public
	 * @param bool $self include the current loaded node?
	 * @param string $direction direction to order the left column by.
	 * @return ORM_MPTT
	 */
	public function siblings($self = FALSE, $direction = 'ASC')
	{
		$siblings = ORM_MPTT::factory($this->_object_name)
							->where($this->_left_column, '>', $this->parent->{$this->_left_column})
							->where($this->_right_column, '<', $this->parent->{$this->_right_column})
							->where($this->_scope_column, '=', $this->{$this->_scope_column})
							->where($this->_level_column, '=', $this->{$this->_level_column})
							->order_by($this->_left_column, $direction);
		
		if ( ! $self)
		{
			$siblings->where($this->_primary_key, '<>', $this->{$this->_primary_key});
		}
		
		return $siblings;
	}
	
	/**
	 * Returns leaves under the current node.
	 *
	 * @access public
	 * @return ORM_MPTT
	 */
	public function leaves($self = FALSE, $direction = 'ASC')
	{
		return $this->descendants($self, $direction, TRUE, TRUE);
	}
	
	/**
	 * Get Size
	 *
	 * @access protected
	 * @return integer
	 */
	protected function get_size()
	{
		return ($this->{$this->_right_column} - $this->{$this->_left_column}) + 1;
	}

	/**
	 * Create a gap in the tree to make room for a new node
	 *
	 * @access private
	 * @param integer $start start position.
	 * @param integer $size the size of the gap (default is 2).
	 */
	private function create_space($start, $size = 2)
	{
		// Update the left values, then the right.
		DB::update($this->_table_name)
			->set(array($this->_left_column => new Database_Expression('`'.$this->_left_column.'` + '.$size)))
			->where($this->_left_column, '>=', $start)
			->where($this->_scope_column, '=', $this->{$this->_scope_column})
			->execute((string) $this->_db);
			
		DB::update($this->_table_name)
			->set(array($this->_right_column => new Database_Expression('`'.$this->_right_column.'` + '.$size)))
			->where($this->_right_column, '>=', $start)
			->where($this->_scope_column, '=', $this->{$this->_scope_column})
			->execute((string) $this->_db);
	}
	
	/**
	 * Closes a gap in a tree. Mainly used after a node has
	 * been removed.
	 *
	 * @access private
	 * @param integer $start start position.
	 * @param integer $size the size of the gap (default is 2).
	 */
	private function delete_space($start, $size = 2)
	{
		// Update the left values, then the right.
		DB::update($this->_table_name)
			->set(array($this->_left_column => new Database_Expression('`'.$this->_left_column.'` - '.$size)))
			->where($this->_left_column, '>=', $start)
			->where($this->_scope_column, '=', $this->{$this->_scope_column})
			->execute((string) $this->_db);
			
		DB::update($this->_table_name)
			->set(array($this->_right_column => new Database_Expression('`'.$this->_right_column.'` - '.$size)))
			->where($this->_right_column, '>=', $start)
			->where($this->_scope_column, '=', $this->{$this->_scope_column})
			->execute((string) $this->_db);
	}
	
	/**
	 * Insert this object as the root of a new scope
	 *
	 * @param integer $scope New scope to create.
	 * @return ORM_MPTT
	 **/
	public function insert_as_new_root($scope = 1)
	{
		// Make sure the specified scope doesn't already exist.
		$root = $this->root($scope);

		if ($root->_loaded)
			return FALSE;
		
		// Create a new root node in the new scope.
		$this->{$this->_left_column} = 1;
		$this->{$this->_right_column} = 2;
		$this->{$this->_level_column} = 0;
		$this->{$this->_scope_column} = $scope;
		
		parent::save();
		
		return $this;
	}
	
	/**
	 * Insert the object
	 * 
	 * ORM_MPTT|mixed $target target node primary key value or ORM_MPTT object. 
	 * @param string $copy_left_from target object property to take new left value from
	 * @param integer $left_offset offset for left value
	 * @param integer $level_offset offset for level value
	 * @access protected
	 * @return ORM_MPTT
	 */
	
	protected function insert($target, $copy_left_from, $left_offset, $level_offset)
	{
		$this->_load();
		
		// Insert should only work on new nodes.. if its already it the tree it needs to be moved!
		if ($this->_loaded)
			return FALSE;
		
		$this->lock();
		
		if ( ! $target instanceof $this)
		{
			$target = ORM_MPTT::factory($this->_object_name, $target);
		}
		else
		{
			$target->reload();
		}
		
		
		$this->{$this->_left_column}  = $target->{$copy_left_from} + $left_offset;
		$this->{$this->_right_column} = $this->{$this->_left_column} + 1;
		$this->{$this->_level_column} = $target->{$this->_level_column} + $level_offset;
		$this->{$this->_scope_column} = $target->{$this->_scope_column};
		
		$this->create_space($this->{$this->_left_column});
		
		parent::save();
		
		$this->unlock();
		
		return $this;
	}
	
	/**
	 * Inserts a new node as the first child of the target node
	 *
	 * @access public
	 * @param ORM_MPTT|mixed $target target node primary key value or ORM_MPTT object.
	 * @return ORM_MPTT
	 */
	public function insert_as_first_child($target)
	{
		return $this->insert($target, $this->_left_column, 1, 1);
	}
	
	/**
	 * Inserts a new node as the last child of the target node
	 *
	 * @access public
	 * @param ORM_MPTT|mixed $target target node primary key value or ORM_MPTT object.
	 * @return ORM_MPTT
	 */
	public function insert_as_last_child($target)
	{
		return $this->insert($target, $this->_right_column, 0, 1);
	}

	/**
	 * Inserts a new node as a previous sibling of the target node.
	 *
	 * @access public
	 * @param ORM_MPTT|integer $target target node id or ORM_MPTT object.
	 * @return ORM_MPTT
	 */
	public function insert_as_prev_sibling($target)
	{
		return $this->insert($target, $this->_left_column, 0, 0);
	}

	/**
	 * Inserts a new node as the next sibling of the target node.
	 *
	 * @access public
	 * @param ORM_MPTT|integer $target target node id or ORM_MPTT object.
	 * @return ORM_MPTT
	 */
	public function insert_as_next_sibling($target)
	{	
		return $this->insert($target, $this->_right_column, 1, 0);
	}
	
	/**
	 * Removes a node and it's descendants.
	 *
	 * @access public
	 */
	public function delete($id = NULL)
	{
		$this->lock();
		
		DB::delete($this->_table_name)
			->where($this->_left_column, '>=', $this->{$this->_left_column})
			->where($this->_right_column, '<=', $this->{$this->_right_column})
			->where($this->_scope_column, '=', $this->{$this->_scope_column})
			->execute((string) $this->_db);
		
		$this->delete_space($this->{$this->_left_column}, $this->get_size());
		
		$this->unlock();
	}

	/**
	 * Overloads the select_list method to
	 * support indenting.
	 * 
	 * Returns all recods in the current scope
	 *
	 * @param string $key first table column.
	 * @param string $val second table column.
	 * @param string $indent character used for indenting.
	 * @return array 
	 */
	public function select_list($key = 'id', $value = 'name', $indent = NULL)
	{
		$result = DB::select($key, $value, $this->_level_column)
			->from($this->_table_name)
			->where($this->_scope_column, '=', $this->{$this->_scope_column})
			->order_by($this->_left_column, 'ASC')
			->execute((string) $this->_db);
			
		if (is_string($indent))
		{		
			$array = array();
			
			foreach ($result as $row)
			{
				$array[$row[$key]] = str_repeat($indent, $row[$this->_level_column]).$row[$value];
			}
			
			return $array;
		}

		return $result->as_array($key, $value);
	}
	
	/**
	 * Move to First Child
	 *
	 * Moves the current node to the first child of the target node.
	 *
	 * @param ORM_MPTT|integer $target target node id or ORM_MPTT object.
	 * @return ORM_MPTT
	 */
	public function move_to_first_child($target)
	{
		return $this->move($target, TRUE, 1, 1, TRUE);
	}
	
	/**
	 * Move to Last Child
	 *
	 * Moves the current node to the last child of the target node.
	 *
	 * @param ORM_MPTT|integer $target target node id or ORM_MPTT object.
	 * @return ORM_MPTT
	 */
	public function move_to_last_child($target)
	{	
		return $this->move($target, FALSE, 0, 1, TRUE);
	}
	
	/**
	 * Move to Previous Sibling.
	 *
	 * Moves the current node to the previous sibling of the target node.
	 *
	 * @param ORM_MPTT|integer $target target node id or ORM_MPTT object.
	 * @return ORM_MPTT
	 */
	public function move_to_prev_sibling($target)
	{	
		return $this->move($target, TRUE, 0, 0, FALSE);
	}
	
	/**
	 * Move to Next Sibling.
	 *
	 * Moves the current node to the next sibling of the target node.
	 *
	 * @param ORM_MPTT|integer $target target node id or ORM_MPTT object.
	 * @return ORM_MPTT
	 */
	public function move_to_next_sibling($target)
	{
		return $this->move($target, FALSE, 1, 0, FALSE);
	}
	
	/**
	 * Move
	 *
	 * @param ORM_MPTT|integer $target target node id or ORM_MPTT object.
	 * @param bool $left_column use the left column or right column from target
	 * @param integer $left_offset left value for the new node position.
	 * @param integer $level_offset level
	 * @param bool allow this movement to be allowed on the root node
	 */
	protected function move($target, $left_column, $left_offset, $level_offset, $allow_root_target)
	{
		$this->_load();
		
		if ( ! $this->_loaded)
			return FALSE;
		
		// Make sure we have the most upto date version of this AFTER we lock
		$this->lock();
		$this->reload();
		
		if ( ! $target instanceof $this)
		{
			$target = ORM_MPTT::factory($this->_object_name, $target);
		}
		
		// Stop $this being moved into a descendant or itself or disallow if target is root
		if ($target->is_descendant($this) OR $this->{$this->_primary_key} === $target->{$this->_primary_key} OR ($allow_root_target === FALSE AND $target->is_root()))
		{
			$this->unlock();
			return FALSE;
		}
		
		$left_offset = ($left_column === TRUE ? $target->{$this->_left_column} : $target->{$this->_right_column}) + $left_offset;
		$level_offset = $target->{$this->_level_column} - $this->{$this->_level_column} + $level_offset;

		$size = $this->get_size();
		
		$this->create_space($left_offset, $size);

		// if node is moved to a position in the tree "above" its current placement
		// then its lft/rgt may have been altered by create_space
		$this->reload();
		
		$offset = ($left_offset - $this->{$this->_left_column});
		
		// Update the values.
		Database::instance((string) $this->_db)->query(NULL, 'UPDATE '.$this->_table_name.' 
			SET `'.$this->_left_column.'` = `'.$this->_left_column.'` + '.$offset.', `'.$this->_right_column.'` = `'.$this->_right_column.'` + '.$offset.'
			, `'.$this->_level_column.'` = `'.$this->_level_column.'` + '.$level_offset.'
			, `'.$this->_scope_column.'` = '.$target->{$this->_scope_column}.' 
			WHERE `'.$this->_left_column.'` >= '.$this->{$this->_left_column}.' 
			AND `'.$this->_right_column.'` <= '.$this->{$this->_right_column}.' 
			AND `'.$this->_scope_column.'` = '.$this->{$this->_scope_column}, TRUE);
		
		$this->delete_space($this->{$this->_left_column}, $size);

		$this->unlock();
		
		return $this;
	}
	
	/**
	 *
	 * @access public
	 * @param $column - Which field to get.
	 * @return mixed
	 */
	public function __get($column)
	{
		switch ($column)
		{
			case 'parent':
				return $this->parent();
			case 'parents':
				return $this->parents();
			case 'children':
				return $this->children();
			case 'first_child':
				return $this->children(FALSE, 'ASC', 1);
			case 'last_child':
				return $this->children(FALSE, 'DESC', 1);
			case 'siblings':
				return $this->siblings();
			case 'root':
				return $this->root();
			case 'leaves':
				return $this->leaves();
			case 'descendants':
				return $this->descendants();
			default:
				return parent::__get($column);
		}
	}
	
	/**
	 * Verify the tree is in good order 
	 * 
	 * This functions speed is irrelevant - its really only for debugging and unit tests
	 * 
	 * @todo Look for any nodes no longer contained by the root node.
	 * @todo Ensure every node has a path to the root via ->parents(); 
	 * @access public
	 * @return boolean
	 */
	public function verify_tree()
	{
		foreach ($this->get_scopes() as $scope)
		{
			if ( ! $this->verify_scope($scope->{$this->_scope_column}))
				return FALSE;
		}
		return TRUE;
	}
	
	private function get_scopes()
	{
		// TODO... redo this so its proper :P and open it public
		// used by verify_tree()
		return DB::select()->as_object()->distinct($this->_scope_column)->from($this->_table_name)->execute((string) $this->_db);
	}
	
	public function verify_scope($scope)
	{
		$root = $this->root($scope);
		
		$end = $root->{$this->_right_column};
		
		// Find nodes that have slipped out of bounds.
		$result = Database::instance((string) $this->_db)->query(NULL, 'SELECT count(*) as count FROM `'.$this->_table_name.'`
			WHERE `'.$this->_scope_column.'` = '.$root->{$this->_scope_column}.' AND (`'.$this->_left_column.'` > '.$end.' 
			OR `'.$this->_right_column.'` > '.$end.')', TRUE);
		
		if ($result > 0)
			return FALSE;
		
		// Find nodes that have the same left and right value
		$result = Database::instance((string) $this->_db)->query(NULL, 'SELECT count(*) as count FROM `'.$this->_table_name.'` 
			WHERE `'.$this->_scope_column.'` = '.$root->{$this->_scope_column}.' 
			AND `'.$this->_left_column.'` = `'.$this->_right_column.'`', TRUE);
		
		if ($result > 0)
			return FALSE;
		
		// Find nodes that right value is less than the left value
		$result = Database::instance((string) $this->_db)->query(NULL, 'SELECT count(*) as count FROM `'.$this->_table_name.'` 
			WHERE `'.$this->_scope_column.'` = '.$root->{$this->_scope_column}.' 
			AND `'.$this->_left_column.'` > `'.$this->_right_column.'`', TRUE);

		if ($result > 0)
			return FALSE;
		
		// Make sure no 2 nodes share a left/right value
		$i = 1;
		while ($i <= $end)
		{
			$result = Database::instance((string) $this->_db)->query(NULL, 'SELECT count(*) as count FROM `'.$this->_table_name.'` 
				WHERE `'.$this->_scope_column.'` = '.$root->{$this->_scope_column}.' 
				AND (`'.$this->_left_column.'` = '.$i.' OR `'.$this->_right_column.'` = '.$i.')', TRUE);
			
			if ($result > 1)
				return FALSE;
				
			$i++;
		}
		
		// Check to ensure that all nodes have a "correct" level
		
		return TRUE;
	}
	
	/**
	 * Force object to reload MPTT fields from database
	 * 
	 * @return $this
	 */
	public function reload()
	{
		$this->_load();
		
		if ( ! $this->_loaded) 
		{
			return FALSE;
		}
		
		$mptt_vals = DB::select(
				$this->_left_column,
				$this->_right_column,
				$this->_level_column,
				$this->_scope_column
			)
			->from($this->_table_name)
			->where($this->_primary_key, '=', $this->{$this->_primary_key})
			->execute((string) $this->_db)
			->current();
		
		return $this->values($mptt_vals);
	}
		
	/**
	 * Generates the HTML for this node's descendants
	 *
	 * @param string $style pagination style.
	 * @param boolean $self include this node or not.
	 * @param string $direction direction to order the left column by.
	 * @return View
	 */
	public function render_descendants($style = NULL, $self = FALSE, $direction = 'ASC')
	{
		$nodes = $this->descendants($self, $direction);
		
		if ($style === NULL)
		{
			$style = $this->_style;
		}

		return View::factory($this->_directory.DIRECTORY_SEPARATOR.$style, array('nodes' => $nodes,'_level_column' => $this->_level_column));
	}
	
	/**
	 * Generates the HTML for this node's children
	 *
	 * @param string $style pagination style.
	 * @param boolean $self include this node or not.
	 * @param string $direction direction to order the left column by.
	 * @return View
	 */
	public function render_children($style = NULL, $self = FALSE, $direction = 'ASC')
	{
		$nodes = $this->children($self, $direction);
		
		if ($style === NULL)
		{
			$style = $this->_style;
		}

		return View::factory($this->_directory.DIRECTORY_SEPARATOR.$style, array('nodes' => $nodes,'_level_column' => $this->_level_column));
	}
}