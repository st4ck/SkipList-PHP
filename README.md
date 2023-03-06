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

$arr = array("B","C");
$skiplist->addMany($arr);

$randomValues = $skiplist->getRandomM(2); // return 2 random values from the SkipList
?>
```

## Complexity
When accessing the SkipList as an array, consider that specifying an offset the complexity is O(n) because the main level must be traversed completely counting the number of elements.

## Exceptions
When using as an array, if you change the value of an element (eg. $skiplist[1] = "1"), the old value at that offset will be removed and the new element will be added in the correct position because the skiplist must remain sorted.

## Implementation details
Class SkipNode represents the nodes in the SkipList. Each node has a value and an array called "forward" which holds references to nodes in the next level

### Constructor:
Accepts an optional parameter maxLevel, which sets the maximum level of the SkipList (default 10).

Initializes the head of the SkipList with a null value and a forward array of size maxLevel + 1.

Initializes other class attributes, such as level, position, and the number of elements.

### Iterator methods:
current(): Returns the value of the current position in the SkipList.

key(): Returns null since there is no associated key in the SkipList.

next(): Moves the current position pointer to the next node in the SkipList.

rewind(): Resets the current position pointer to the first node in the SkipList.

valid(): Returns true if the current position pointer is not null and false otherwise.


### ArrayAccess methods:
offsetExists(): Accepts an offset parameter and returns true if the value associated with the offset exists in the SkipList and false otherwise.

offsetGet(): Accepts an offset parameter and returns the value associated with the offset in the SkipList.

offsetSet(): Accepts an offset and a value parameter and adds the value to the SkipList. (see Exceptions)

offsetUnset(): Accepts an offset parameter and removes the value associated with the offset from the SkipList.


### Main methods:
getRandom(): Returns a random value from the SkipList.

getRandomM(): Accepts a number parameter and returns an array of "number" random values from the SkipList.

randomLevel(): Returns a random level between 0 and the maximum level.

count(): Returns the number of elements in the SkipList.

addMany(): Accepts an array parameter and adds all its values to the SkipList.

toArray(): Returns an array of all the values in the SkipList.

search(): Accepts a value parameter and searches for its position in the SkipList.

add(): Accepts a value parameter and adds it to the SkipList.

delete(): Accepts a value parameter and removes it from the SkipList.
