<?php

  namespace phrames\models\fields;

  abstract class OnDelete {

    const CASCADE = 1;
    const PROTECT = 2;
    const SET_NULL = 3;
    const SET_DEFAULT = 4;
    const DO_NOTHING = 5;

  };
