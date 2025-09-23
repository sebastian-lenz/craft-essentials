<?php

namespace lenz\craft\essentials\services\tables;

enum FilterType
{
  case Any;
  case FromInput;
  case FromStorage;
  case ToStorage;
}
