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
current(): Returns the value of the current position in the SkipList. Complexity O(1).

key(): Returns null since there is no associated key in the SkipList. Complexity O(1).

next(): Moves the current position pointer to the next node in the SkipList. Complexity O(1).

rewind(): Resets the current position pointer to the first node in the SkipList. Complexity O(1).

valid(): Returns true if the current position pointer is not null and false otherwise. Complexity O(1).


### ArrayAccess methods:
offsetExists(): Accepts an offset parameter and returns true if the value associated with the offset exists in the SkipList and false otherwise. Complexity O(n).

offsetGet(): Accepts an offset parameter and returns the value associated with the offset in the SkipList. Complexity O(n).

offsetSet(): Accepts an offset and a value parameter and adds the value to the SkipList. (see Exceptions). Complexity O(n).

offsetUnset(): Accepts an offset parameter and removes the value associated with the offset from the SkipList. Complexity O(n).


### Main methods:
getRandom(): Returns a random value from the SkipList. Complexity O(n).

getRandomM(): Accepts a number parameter and returns an array of "number" random values from the SkipList. Complexity O(n).

randomLevel(): Returns a random level between 0 and the maximum level.

count(): Returns the number of elements in the SkipList. Complexity O(1).

addMany(): Accepts an array parameter and adds all its values to the SkipList. Complexity O(m log n).

toArray(): Returns an array of all the values in the SkipList. Complexity O(n).

search(): Accepts a value parameter and searches for its position in the SkipList. Complexity O(log n).

add(): Accepts a value parameter and adds it to the SkipList. Complexity O(log n).

delete(): Accepts a value parameter and removes it from the SkipList. Complexity O(log n).

printList(): Print the skip list without considering spacing. Just a raw print of all levels.
