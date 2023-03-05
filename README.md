# SkipList-PHP
SkipList implementation in PHP. The class is iterable and accessible as a common array (with few exceptions).

## Usage
Just include the skip_list.php file to your project and you can start to use the implementation. Small example below.

```
<?php
require("skip_list.php");
$skiplist = new SkipList(20); // 20 levels chosen
$skiplist->add("Z");
$skiplist->add("A");
print $skiplist[0]."\n"; // A printed

foreach ($skiplist as $val) {
 print $val."\n";
}

var_dump($skiplist->toArray());

$skiplist->addMany(array("B","C"));

$randomValues = $skiplist->getRandomM(2); // return 2 random values from the SkipList
?>
```

## Complexity
When accessing the SkipList as an array, consider that specifying an offset the complexity is O(n) because the main level must be traversed completely counting the number of elements.

## Exceptions
When using as an array, if you change the value of an element (eg. $skiplist[1] = "1"), the old value at that offset will be removed and the new element will be added in the correct position because the skiplist must remain sorted.
