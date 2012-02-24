<?php

  namespace phrames\db\drivers;

  use phrames\Config_phrames as Config_phrames;
  use phrames\query\QueryBuilder as QueryBuilder;
  use phrames\query\QuerySet as QuerySet;
  use phrames\query\Field as Field;
  use phrames\model\ForeignKey as ForeignKey;

  class DB_MYSQL implements \phrames\db\drivers\DB_Driver {

    /**
     * Stores the PDO connection for this database driver
     *
     * @var PDO
     */
    private static $conn = null;

    /**
     * Return an array of valid expression operators.
     * This is in a static function because PHP does not
     * allow closures to be defined in a static array
     * parameter.
     *
     * Each array member defines an anonymous function
     * that parses the particular expression in valid
     * SQL statement portion
     *
     * @return array
     */
    public static function operators() {
      return array( 
        "EXACT" => function($builder, $f, $v) {
          return "{$f} = {$builder->hash($v)}";
        },
        "IEXACT" => function($builder, $f, $v) {
          return "{$f} ILIKE {$builder->hash($v)}";
        },
        "CONTAINS" => function($builder, $f, $v) {
          return "{$f} LIKE " . $builder->hash("%{$v}%");
        },
        "ICONTAINS" => function($builder, $f, $v) {
          return "{$f} ILIKE " . $builder->hash("%{$v}%");
        },
        "IN" => function($builder, $f, $v) {

          if ($v instanceof QuerySet) {
            $model = $v->get_class();
            $v = $v->value_list($model::get_id_field());
          }

          if (!is_array($v) || !sizeof($v)) {
            return "{$f} IN (NULL)";
          } else {
            $callback = function(&$i) use ($builder) {
              if (!is_int($i)) 
                $i = $builder->hash($i);
            };
            array_walk($v, $callback);
            return "{$f} IN (" . implode(", ", $v) . ")";
          }
        },
        "GT" => function($builder, $f, $v) {
          return "{$f} > {$builder->hash($v)}";
        },
        "GTE" => function($builder, $f, $v) {
          return "{$f} >= {$builder->hash($v)}";
        },
        "LT" => function($builder, $f, $v) {
          return "{$f} < {$builder->hash($v)}";
        },
        "LTE" => function($builder, $f, $v) {
          return "{$f} >= {$builder->hash($v)}";
        },
        "STARTSWITH" => function($builder, $f, $v) {
          return "{$f} LIKE " . $builder->hash("{$v}%");
        },
        "ISTARTSWITH" => function($builder, $f, $v) {
          return "{$f} ILIKE " . $builder->hash("{$v}%");
        },
        "ENDSWITH" => function($builder, $f, $v) {
          return "{$f} LIKE " . $builder->hash("%{$v}");
        },
        "IENDSWITH" => function($builder, $f, $v) {
          return "{$f} ILIKE " . $builder->hash("%{$v}");
        },
        "RANGE" => function($builder, $f, $v) {
          if (is_array($v) && sizeof($v) == 2) 
            return "{$f} BETWEEN {$builder->hash($v[0])} AND {$builder->hash($v[1])}";
          else 
            throw new \Exception("Invalid RANGE expression for {$f}: " .
                "must be an array with two items.");
        },
        "YEAR" => function($builder, $f, $v) {
          return "EXTRACT('year' FROM {$f}) = {$builder->hash($v)}";
        },
        "MONTH" => function($builder, $f, $v) {
          return "EXTRACT('month' FROM {$f}) = {$builder->hash($v)}";
        },
        "DAY" => function($builder, $f, $v) {
          return "EXTRACT('day' FROM {$f}) = {$builder->hash($v)}";
        },
        "WEEK_DAY" => function($builder, $f, $v) {
          return "EXTRACT('dayofweek' FROM {$f} = {$builder->hash($v)}";
        },
        "ISNULL" => function($builder, $f, $v) {
          if ($v = true)
            return "{$f} IS NULL";
          else
            return "{$f} IS NOT NULL";
        },
        "REGEX" => function($builder, $f, $v) {
          return "{$f} REGEXP BINARY {$builder->hash($v)}";
        },
        "IREGEX" => function($builder, $f, $v) {
          return "{$f} REGEXP {$builder->hash($v)}";
        },
      );
    }

    /**
     * Returns all valid operators by their names only
     *
     * @return array
     */
    public static function get_operators() {
      return array_keys(self::operators());
    }

    /**
     * As with operators(), defines all valid mathematical
     * operators and their parsing functions
     *
     * @return array
     */
    public static function math_operators() {
      return array(
        "TIMES" => function($builder, $f, $v) {
          return "{$f} * {$builder->hash($v)}";
        },
        "DIVIDED_BY" => function($builder, $f, $v) {
          return "{$f} / {$builder->hash($v)}";
        },
        "PLUS" => function($builder, $f, $v) {
          return "{$f} + {$builder->hash($v)}";
        },
        "MINUS" => function($builder, $f, $v) {
          return "{$f} - {$builder->hash($v)}";
        },
      );
    }

    /**
     * Returns all valid math operators by their names only
     *
     * @return array
     */
    public static function get_math_operators() {
      return array_keys(self::math_operators());
    }

    /**
     * Construct a new driver object and initialize its
     * database connection
     */
    public function __construct() {
      if (!self::$conn instanceof \PDO)
        self::$conn = new \PDO(
            "mysql:host=" . Config_phrames::DB_HOST .
              ";dbname=" . Config_phrames::DB_NAME,
            Config_phrames::DB_USER,
            Config_phrames::DB_PASS
        );
    }

    /**
     * Return all of the column values for a particular row by ID
     *
     * @param string $table
     * @param string $id_field
     * @param int $id
     * @return array
     */
    public function get_row($table, $id_field, $id) {
      return self::$conn->query("SELECT * FROM {$table} WHERE {$id_field}={$id}")
        ->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Insert a new row into a database table
     * 
     * @param string $table
     * @param string $id_field
     * @param array $data
     * @return int New row ID for object 
     */
    public function insert_row($table, $id_field, $data) {
      $stmt = "INSERT INTO {$table} ";
      if (sizeof($data)) {
        // format column names
        $stmt .= "(" . implode(", ", array_keys($data)) . ") " .
          "VALUES (";
        // format column values
        foreach(array_keys($data) as $field)
          $stmt .= ":{$field}, ";
        $stmt = substr(trim($stmt), 0, -1);
        $stmt .= ")";

        $stmt = self::$conn->prepare($stmt);
        foreach($data as $field => $value)
          $stmt->bindValue(":{$field}", $value);
      } else {
        $stmt = self::$conn->prepare($stmt);
      }
      $stmt->execute();

      $id = self::$conn->query("SELECT {$id_field} FROM {$table} " .
          "ORDER BY {$id_field} DESC LIMIT 0,1")->fetch(\PDO::FETCH_ASSOC);
      return $id[$id_field];
    }

    /**
     * Update an existing row in a database table using a particular
     * id field
     *
     * @param string $table
     * @param string $id_field
     * @param int $id
     * @param array $data
     * @return int
     */
    public function update_row($table, $id_field, $id, $data) {
      // create statement
      $stmt = "UPDATE {$table} SET ";
      foreach(array_keys($data) as $field)
        $stmt .= "{$field} = :{$field}, ";
      $stmt = substr(trim($stmt), 0, -1);
      $stmt .= " WHERE {$id_field} = {$id}"; 
      // bind params
      $stmt = self::$conn->prepare($stmt);
      foreach($data as $field => $value)
        $stmt->bindValue(":{$field}", $value);
      $stmt->execute();
      return $id;
    }

    /**
     * Delete a single row from the database given an ID
     *
     * @param string $table
     * @param string $id_field
     * @param int $id
     */
    public function delete_row($table, $id_field, $id) {
      self::$conn->exec("DELETE FROM {$table} WHERE {$id_field} = {$id}");
    }

    public function field_parse(QueryBuilder $builder, QuerySet $query, Field $field) {
      // add table alias as md5("table") since we do not know yet
      // what the primary table will be -- and the odds of someone
      // having a table named md5("table") will, hopefully, be zero
      $class = $query->get_class();

      if ($field->get_through()) {
        $joins = $class::get_joins();
        $table = $joins[$field->get_through()]::table_name();
        $builder->add_join($table);
      } else {
        $table = $class::table_name();
      }
      return "{$table}.{$field->get_field()}";
    }

    /**
     * Parse a query expression and build all required PDO parameters to bind,
     * return the generated expression string
     *
     * @param QueryBuilder $builder
     * @param Expression $exp
     * @return string
     */
    public function expression_parse(QueryBuilder $builder, QuerySet $query,
        \phrames\query\Expression $exp) {
      if ($exp instanceof \phrames\query\ExpressionMath) {
        $operators = self::math_operators();
      } else {
        $operators = self::operators();
      }

      // get parser function
      $parser = $operators[$exp->get_operator()];

      // field name
      $field = $this->field_parse($builder, $query, $exp->get_field());

      // return sql expression portion string
      if ($exp->get_value() instanceof \phrames\query\ExpressionMath) {
        $value = $this->expression_parse($builder, $query, $exp->get_value());
        $builder->dont_hash($value);
      } else {
        $value = $exp->get_value();
      }
      return ($exp->is_not() ? "NOT " : "") . $parser($builder, $field, $value);
    }

    /**
     * Parse an expression node and all of it's children expressions, return
     * the generated expression
     *
     * @param QueryBuilder $builder
     * @param ExpressionNode $node
     * @return string
     */
    public function expressionnode_parse(QueryBuilder $builder, QuerySet $query,
        ExpressionNode $node) {
      $parts = array();
      foreach($node->get_expressions() as $exp) {
        if ($exp instanceof \phrames\query\ExpressionNode) {
          $parts[] = "({$this->expressionnode_parse($builder, $query, $exp)})";
        } else {
          $parts[] = $this->expression_parse($builder, $query, $exp); 
        }
      }
      $stmt = implode(($node instanceof \phrames\query\ExpressionAnd ? " AND " : " OR "), $parts);
      if (sizeof($parts)) $stmt = ($node->is_not() ? "NOT ({$stmt})" : $stmt);
      return $stmt;
    }

    /**
     * Parse a query including all of it's parent (linked list) expressions
     * into a fully formed MySQL query
     *
     * @param QueryBuilder $builder
     * @param QuerySet $query
     * @return string
     */
    public function queryset_parse(QueryBuilder $builder, QuerySet $query) {
      $stmt = "";

      // parse any parent query first to prepend them to the generated statement
      if ($query->get_parent() instanceof QuerySet) {
        $stmt .= $this->queryset_parse($builder, $query->get_parent());
        if (sizeof($query->get_args()) 
            && $query->get_parent() instanceof QuerySet 
            && sizeof($query->get_parent()->get_args())) {
          $stmt = "(" . $stmt;
          if ($query instanceof \phrames\query\QueryExclude)
            $stmt .= ") AND ";
          else
            $stmt .= ") AND ";
        }
      }

      // build each individual expression and expression node
      $parts = array();
      foreach($query->get_args() as $arg) { 
        if ($arg instanceof \phrames\query\Expression)
          $parts[] = $this->expression_parse($builder, $query, $arg);
        elseif ($arg instanceof \phrames\query\ExpressionNode)
          $parts[] = "(" . $this->expressionnode_parse($builder, $query, $arg) . ")";
      }

      // piece all of the parts (expressions) together
      if (sizeof($parts)) {
        if ($query instanceof \phrames\query\QueryExclude)
          $stmt .= "NOT ";

        if (sizeof($parts) > 1)
          $stmt .= "(" . implode(" AND ", $parts) . ")";
        else
          $stmt .= $parts[0];
        return $stmt;
      } else {
        // empty query
        return "";
      }

    }

    /**
     * Return a set of index/primary keys for a particular query on
     * a single model/table
     *
     * @param QuerySet $query
     * @return array
     */
    public function get_keys(QuerySet $query) {
      $builder = new QueryBuilder();
      $model = $query->get_class(); 

      $select = "SELECT {$model::table_name()}.{$model::get_id_field()} " . 
        "FROM {$model::table_name()}";

      // need to parse the actual query first to determine what
      // joins are actually required
      $q = $this->queryset_parse($builder, $query);

      $joins = "";
      $required_joins = $builder->get_joins();
      foreach($model::get_joins() as $field => $join)
        if (in_array($join::table_name(), $required_joins))
          $joins .= "LEFT JOIN {$join::table_name()} " .
            "ON {$join::table_name()}.{$join::get_id_field()} = " .
            "{$model::table_name()}.{$field} ";

      $stmt = "{$select} {$joins}";

      // order_by?
      $order_by = array();
      $orders = $query->get_order_by();
      if (sizeof($orders)) {
        foreach($orders as $order) { 
          if ($order[0] == "-")
            $order_by[] = "{$model::table_name()}." . substr($order, 1) . " DESC";
          else
            $order_by[] = "{$model::table_name()}.{$order}";
        }
        $order_by = "ORDER BY " . implode(", ", $order_by);
      } else {
        $order_by = "";
      }

      if (strlen($q))
        $stmt .= "WHERE {$q} {$order_by}";
      else
        $stmt .= "{$order_by}";

      $limit = $query->get_limit();
      if (sizeof($limit))
        $stmt .= "LIMIT " . implode(",", $limit);

      $stmt = self::$conn->prepare(trim($stmt));

      if (strlen($q))
        foreach($builder->get_params() as $param => $value)
          $stmt->bindValue(":{$param}", $value);

      if (!$stmt->execute()) {
        return array();
      } else {
        return array_map("intval", array_values($stmt->fetchAll(\PDO::FETCH_COLUMN))); 
      }
    }

    /**
     * Return a result count of a particular query
     *
     * @param QuerySet $query
     * @return int
     */
    public function get_query_count(QuerySet $query) {
      $builder = new QueryBuilder();
      $model = $query->get_class();

      $select = "SELECT COUNT({$model::table_name()}.{$model::get_id_field()}) as count " .
              "FROM {$model::table_name()}";

      $q = $this->queryset_parse($builder, $query);

      $required_joins = $builder->get_joins();
      $joins = "";
      foreach($model::get_joins() as $field => $join)
        if (in_array($join::table_name(), $required_joins))
          $joins .= "LEFT JOIN {$join::table_name()} " .
            "ON {$join::table_name()}.{$join::get_id_field()} = " .
            "{$model::table_name()}.{$field} ";

      $stmt = "{$select} {$joins}";

      if (strlen($q))
        $stmt .= "WHERE {$q} ";

      // bind params
      $stmt = self::$conn->prepare(trim($stmt));

      if (strlen($q))
        foreach($builder->get_params() as $param => $value)
          $stmt->bindValue(":{$param}", $value);

      if (!$stmt->execute()) {
        return 0;
      } else {
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $actual_count = (int) $result["count"];
        // since you can't actually COUNT(id) using LIMIT, get
        // a theoretical count of results
        $theoretical_count = $query->get_limit_count();

        if (!$theoretical_count || $actual_count < $theoretical_count)
          return $actual_count;
        else
          return $theoretical_count;
      }
    }

    /**
     * Create a MySQL CREATE TABLE statement
     * using the defined fields of a particular Model
     *
     * @param string $model Model name
     * @return string
     */
    public function create_table($model) {
      $pieces = array();
      $stmt = "CREATE TABLE {$model::table_name()}\n(\n";
      foreach($model::get_fields() as $field => $opts) {
        $type = @$opts["type"];
        $not_null = @$opts["required"] ? " NOT NULL" : "";
        if (!($type instanceof \phrames\model\OneToManyField
              || $type instanceof \phrames\model\ManyToManyField)) {
          $class = strtoupper(get_class($type));
          $class = substr($class, strrpos($class, "\\") + 1);
          switch($class) {
            case "IDFIELD":
              $pieces[] = "{$field} INT NOT NULL AUTO_INCREMENT";
              $pieces[] = "PRIMARY KEY ({$field})";
              break;
            case "CHARFIELD":
              $pieces[] = "{$field} VARCHAR({$type->get_length()}){$not_null}";
              break;
            case "JSONFIELD":
            case "TEXTFIELD":
              $pieces[] = "{$field} TEXT{$not_null}";
              break;
            case "DECIMALFIELD":
            case "FLOATFIELD":
              $pieces[] = "{$field} FLOAT{$not_null}";
              break;
            case "BOOLEANFIELD":
              $pieces[] = "{$field} TINYINT(1) UNSIGNED ZEROFILL{$not_null}";
              break;
            case "DATEFIELD":
              $pieces[] = "{$field} DATE{$not_null}";
              break;
            case "DATETIMEFIELD":
              $pieces[] = "{$field} DATETIME{$not_null}";
              break;
            case "TIMEFIELD":
              $pieces[] = "{$field} TIME{$not_null}";
              break;
            case "INTEGERFIELD":
              $pieces[] = "{$field} INT{$not_null}";
              break;
            case "FOREIGNKEY":
              $connects_to = $type->get_connects_to();
              $pieces[] = "{$field} INT{$not_null}, INDEX({$field})";
              $foreign_key = "FOREIGN KEY ({$field}) REFERENCES " .
                "{$connects_to::table_name()}({$connects_to::get_id_field()}) "; 
              if (is_callable($type->get_on_delete()) || $type->get_on_delete() == ForeignKey::SET_DEFAULT)
                $foreign_key .= "ON DELETE NO ACTION";
              elseif ($type->get_on_delete() == ForeignKey::PROTECT)
                $foreign_key .= "ON DELETE RESTRICT";
              elseif ($type->get_on_delete() == ForeignKey::SET_NULL)
                $foreign_key .= "ON DELETE SET NULL";
              else
                $foreign_key .= "ON DELETE CASCADE";
              $pieces[] = $foreign_key;
          }
          if (@$opts["unique"])
            $pieces[] = "UNIQUE ({$field})";
        }
      }
      // statement cleanup, add some tabbing
      $pieces = array_map(function($v) {
          return "\t{$v}";
      }, $pieces);
      $stmt .= implode(",\n", $pieces) . "\n);";
      return $stmt;
    }

  }
