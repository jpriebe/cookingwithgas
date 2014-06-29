<?php
class Application_Model_Recipe extends Application_Model_RecipeObj
{
    protected static $_table_name = 'recipe';
    protected static $_id_name = 'id_recipe';

    protected $_id_account_group = null;
    protected $_title = '';
    protected $_ingredients = '';
    protected $_directions = '';
    protected $_yield = '';
    protected $_source = '';
    protected $_notes = '';
    protected $_tags = array ();
    protected $_images = array ();

    public function get_id_account_group() { return $this->_id_account_group; }
    public function get_title() { return $this->_title; }
    public function get_ingredients() { return $this->_ingredients; }
    public function get_directions() { return $this->_directions; }
    public function get_yield() { return $this->_yield; }
    public function get_source() { return $this->_source; }
    public function get_notes() { return $this->_notes; }
    public function get_tags() { return $this->_tags; }
    public function get_images() { return $this->_images; }

    public static function load ($id)
    {
        $o = Application_Model_RecipeObj::_load (new Application_Model_Recipe(), self::$_table_name, self::$_id_name, $id);
        
        $sql = "SELECT t.id_tag as id_tag, t.tag as tag FROM recipe_to_tag rtt LEFT JOIN tag t ON rtt.id_tag=t.id_tag WHERE rtt.id_recipe = ?";
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $xary = $db->fetchAll ($sql, $o->_id);

        foreach ($xary as $row)
        {
            $o->_tags[] = new Application_Model_Tag ($row['id_tag'], $row['tag']);
        }

        return $o;
    }

    public static function instantiate ($params, $id=null)
    {
        return Application_Model_RecipeObj::_instantiate (new Application_Model_Recipe(), $params, self::$_table_name, self::$_id_name, $id);
    }

    protected function insert ()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction ();
        if (!$this->_insert (self::$_table_name, self::$_id_name))
        {
            $db->rollback ();
            return false;
        }

        foreach ($this->_tags as $tag)
        {
            if (!$this->save_tag ($tag))
            {
                $db->rollback ();
                return false;
            }
        }
        $db->commit ();
        return true;
    }

    private function save_tag ($tag)
    {
        $sql = "SELECT id_tag FROM tag WHERE id_account_group = ? AND tag = ?";
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $xary = $db->fetchAll ($sql, array ($this->_id_account_group, strtolower ($tag)));

        if (count ($xary) == 1)
        {
            $id_tag = $xary[0]['id_tag'];
        }
        else
        {
            $sql = "INSERT INTO tag (id_account_group, tag) VALUES (?, ?)";
            if (!$db->query ($sql, array ($this->_id_account_group, strtolower ($tag))))
            {
                return false;
            }

            $id_tag = $db->lastInsertId ();
        }

        $sql = "INSERT INTO recipe_to_tag (id_recipe, id_tag) VALUES (?, ?)";
        try
        {
            $db->query ($sql, array ($this->_id, $id_tag));
        }
        catch (Exception $e)
        {
            return false;
        }

        return true;
    }

    protected function update ()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $db->beginTransaction ();
        if (!$this->_update (self::$_table_name, self::$_id_name))
        {
            print "failed to update...<br />\n";
            exit;
            $db->rollback ();
            return false;
        }

        $sql = "DELETE FROM recipe_to_tag WHERE id_recipe=?";
        try
        {
            $db->query ($sql, array ($this->_id));
        }
        catch (Exception $e)
        {
            print "failed to clear tags...<br />\n";
            exit;
            $db->rollback ();
            return false;
        }

        foreach ($this->_tags as $tag)
        {
            if (!$this->save_tag ($tag))
            {
                print "failed to save tag $tag...<br />\n";
                exit;
                $db->rollback ();
                return false;
            }
        }
        $db->commit ();
        return true;
    }

    public function delete ()
    {
        return $this->_delete (self::$_table_name, self::$_id_name);
    }

    protected function gather_data ()
    {
        $xary = array (
            'id_account_group' => $this->_id_account_group,
            'title' => $this->_title,
            'ingredients' => $this->_ingredients,
            'directions' => $this->_directions,
            'yield' => $this->_yield,
            'source' => $this->_source,
            'notes' => $this->_notes,
        );

        return $xary;
    }

    public static function search ($id_account_group, $search_terms, $search_by)
    {
        $params = array ($id_account_group);

        $sql = <<<__TEXT__
SELECT DISTINCT r.id_recipe FROM recipe r 
LEFT JOIN recipe_to_tag rtt ON r.id_recipe=rtt.id_recipe
LEFT JOIN tag t ON rtt.id_tag=t.id_tag
WHERE r.id_account_group = ?

__TEXT__;

        $where = array ();
        foreach ($search_by as $sb)
        {
            if ($sb == 'tags')
            {
                $where[] = "t.tag LIKE ?";
            }
            else
            {
                $where[] = "r.$sb LIKE ?";
            }
            $params[] = "%$search_terms%";
        }
        $sql .= " AND (" . join (' OR ', $where) . ")";
        $sql .= " ORDER BY r.title";

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $xary = $db->fetchAll ($sql, $params);

        $retval = array ();

        foreach ($xary as $row)
        {
            $id_recipe = $row['id_recipe'];
            $retval[] = Application_Model_Recipe::load ($id_recipe);
        }

        return $retval;
    }

    /**
     * gets all tags for a particular account group
     * 
     * @param int $id_account_group
     * @return array of Application_Model_Tag objects
     */
    public static function load_all_tags ($id_account_group)
    {
        $sql = "SELECT id_tag, tag FROM tag WHERE id_account_group = ? ORDER BY tag";
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $xary = $db->fetchAll ($sql, $id_account_group);

        $retval = array ();
        foreach ($xary as $row)
        {
            $o = new Application_Model_Tag ($row['id_tag'], $row['tag']);
            $retval[] = $o;
        }

        return $retval;
    }

    /**
     * gets all recipes for a particular account group
     * 
     * @param int $id_account_group
     * @return array of Application_Model_Recipe objects
     */
    public static function load_all ($id_account_group)
    {
        $sql = "SELECT id_recipe FROM recipe WHERE id_account_group = ? ORDER BY title";
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $xary = $db->fetchAll ($sql, $id_account_group);

        $retval = array ();

        foreach ($xary as $row)
        {
            $id_recipe = $row['id_recipe'];
            $retval[] = Application_Model_Recipe::load ($id_recipe);
        }

        return $retval;
    }

    /**
     * gets all recipes for a specified tag id and account group
     * 
     * @param int $id_tag
     * @param int $id_account_group
     * @return array of Application_Model_Recipe objects
     */
    public static function load_all_by_tag ($id_tag, $id_account_group)
    {
        $sql = <<<__TEXT__
SELECT r.id_recipe 
FROM recipe r 
LEFT JOIN recipe_to_tag rtt ON r.id_recipe=rtt.id_recipe
WHERE r.id_account_group = ? 
AND rtt.id_tag = ?
ORDER BY title
__TEXT__;

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $xary = $db->fetchAll ($sql, array ($id_account_group, $id_tag));

        $retval = array ();

        foreach ($xary as $row)
        {
            $id_recipe = $row['id_recipe'];
            $retval[] = Application_Model_Recipe::load ($id_recipe);
        }

        return $retval;
    }

    /**
     * records the access time on a recipe (recipe was viewed or
     * edited by a particular user)
     * 
     * @param int $id_account
     * @return bool true if successful, false otherwise
     */
    public function record_access ($id_account)
    {
        //print "recording access for $id_account\n";
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        try
        {
            //print "deleting...\n";
            $sql = "DELETE FROM recent_recipe WHERE id_recipe = ? AND id_account = ?";
            $db->query ($sql, array ($this->_id, $id_account));

            //print "inserting...\n";
            $sql = "INSERT INTO recent_recipe (id_recipe, id_account) VALUES (?, ?)";
            $db->query ($sql, array ($this->_id, $id_account));


            //print "truncating...\n";

            // truncate list at 25 
            $sql = "SELECT id_recipe FROM recent_recipe where id_account = ? ORDER BY accessed DESC";
            $xary = $db->fetchAll ($sql, array ($id_account));
            $delary = array ();
            foreach ($xary as $row)
            {
                if ($i >= 25)
                {
                    $delary[] = $row['id_recipe'];
                }
                $i++;
            }

            foreach ($delary as $id_recipe);
            {
                $sql = "DELETE FROM recent_recipe WHERE id_recipe = ? AND id_account = ?";
                $db->query ($sql, array ($id_recipe, $id_account));
            }
        }
        catch (Exception $e)
        {
            print "failed to record access time...<br />\n";
            return false;
        }

        return true;
    }


    /**
     * gets most recent recipes
     * 
     * @param int $id_account
     * @return array of Application_Model_Recipe objects
     */
    public static function load_recent ($id_account)
    {
        $sql = <<<__TEXT__
SELECT id_recipe
FROM recent_recipe
WHERE id_account = ?
ORDER BY accessed DESC
__TEXT__;

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $xary = $db->fetchAll ($sql, array ($id_account));

        $retval = array ();

        foreach ($xary as $row)
        {
            $id_recipe = $row['id_recipe'];
            $retval[] = Application_Model_Recipe::load ($id_recipe);
        }

        return $retval;
    }
}

