<?php

    require_once("phrames.php");
    
    use phrames\models as models;
    use phrames\models\fields as fields;

    class Reporter extends models\Model {

      public function __construct($values = []) {
        $this->full_name = new fields\CharField(["max_length" => 70, "default" => "fuck you"]);
        parent::__construct($values);
      }

      public function test() {
        return "test";
      }

    }
    
    class Article extends models\Model {

      public function __construct() {
        $this->test = new fields\CharField(["max_length" => 10]);
        $this->reporter = new fields\ForeignKey("Reporter", ["on_delete" => fields\OnDelete::CASCADE]);
      }
        
    }

  $reporter = new Reporter(['full_name' => 'test']);

  $article = new Article();
  $article->reporter = $reporter;

  var_dump($article);

