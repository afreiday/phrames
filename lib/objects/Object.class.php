<?php

	namespace phrames\objects;

	class Object {

			private $model;

			private $fields = [];

			private $modified_fields = [];

			public function __construct($model) {
					$this->model = $model;
			}

			public function __get($field) {
					return isset($this->fields[$field]) ?
							$this->fields[$field] : null;
			}

			public function __set($field, $value) {
					$this->fields[$field] = $value;
					if (!in_array($field, $this->modified_fields)) {
							$this->modified_fields[] = $field;
					}
			}

	}
