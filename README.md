# SkipList-PHP
SkipList implementation in PHP. The class is iterable and accessible as a common array (with few exceptions).

## Basic usage
Just include the skip_list.php file to your project and you can start to use the implementation. Small examples below.

### Example 1
```
<?php
require("skip_list.php");
$skiplist = new SkipList(20); // 20 levels chosen
$skiplist->add("Z"); // adding Z to SkipList
$skiplist->add("A"); // adding A to SkipList

print $skiplist[0]."\n"; // A printed that is in position 0
print $skiplist->search("A")->val."\n"; // A printed

foreach ($skiplist as $val) { // printing all elements in SkipList
 print $val."\n";
}

var_dump($skiplist->toArray()); // converting SkipList to Array

$arr = array("B","C");
$skiplist->addMany($arr); // adding 2 elements to SkipList

$randomValues = $skiplist->getRandomM(2); // return 2 random values from the SkipList

unset($skiplist[1]); // deleting element in position 1 - B removed
?>
```
This example creates a new SkipList with a maximum level of 20 and adds two values to it. It then prints the value at the first index of the SkipList and searches for the node with the value `"A"` and prints its value. It then iterates over the SkipList and prints each value. It converts the SkipList to array that prints. It add two new elements to the SkipList and retrieve two from the SkipList randomly. Finally, it removes the node at the second index of the SkipList.

### Example 2

If you want to use a custom type for the values in the SkipList, pass a comparator function to the constructor like the example below.
```
<?php
require("skip_list.php");

$myComparator = function ($a, $b, $op) { // defining a custom comparator
	switch ($op) {
		case "<":
			return $a[0] < $b[0];
			break;
		case "===":
			return $a[0] === $b[0];
			break;
	}
};

$skiplist = new SkipList(2,$myComparator); // constructor 2 levels + comparator
$skiplist->add(array("AAA",new SkipList(2))); // adding to the SkipList a array (containing a Key and a new SkipList)
$skiplist->add(array("AAC",new SkipList(1))); // adding to the SkipList a array (containing a Key and a new SkipList)
$skiplist->add(array("BBB",new SkipList(1))); // adding to the SkipList a array (containing a Key and a new SkipList)
$val = $skiplist->search(array("AAA"))->val; // searching for the Key AAA, array ["AAA",SkipList object] retrieved
$val[1]->add("TEST1"); // adding in the SkipList of key "AAA"
$val[1]->add("TEST2"); // adding in the SkipList of key "AAA"
$val = $skiplist->search(array("BBB"))->val; // searching for the Key BBB, array ["BBB",SkipList object] retrieved
$val[1]->add("TEST3"); // adding in the SkipList of key "BBB"

foreach ($skiplist as $val) { // printing all elements in SkipList
 print "-".$val[0]."\n";
 $val[1]->printList(); // calling printList of the internal SkipList
}

$myStringConverter = function ($a) {
 return $a[0];
};

$skiplist->printList($myStringConverter);
?>
```
This example creates a new SkipList with a maximum level of 2 and a custom comparator. It then adds three values to it, where each value is an array that contains two elements: the first element is a string, and the second element is another SkipList. It then searches for the node with the value `["AAA"]` and adds two values to its SkipList. It also searches for the node with the value `["BBB"]` and adds a value to its SkipList. It iterates over the SkipList and prints each value with its corresponding SkipList. The closure passed to the `printList()` method is used to print the values of the inner SkipLists. Finally, it prints the main SkipList using a custom string conversion function (being the values of the SkipList of type `array ["Key",SkipList object]` and not printable)

## Complexity
When accessing the SkipList as an array, consider that specifying an offset the complexity is O(n) because the main level must be traversed completely counting the number of elements.

## Exceptions
When using as an array, if you change the value of an element (eg. $skiplist[1] = "1"), the old value at that offset will be removed and the new element will be added in the correct position because the skiplist must remain sorted.

## Implementation details
Class SkipNode represents the nodes in the SkipList. Each node has a value and an array called "forward" which holds references to nodes in the next level

### Constructor:
It takes two optional parameters: **maxLevel** to set the maximum number of levels of the SkipList and **comparator** to set the closure to compare two values in the SkipList. If **maxLevel** is not provided, it defaults to 10. If **comparator** is not provided, it uses a default closure to compare two values in the SkipList.

Initializes the head of the SkipList with a null value and a forward array of size maxLevel + 1.

Initializes other class attributes, such as level, position, and the number of elements.

### Iterator methods:
**current()**: Returns the value of the current position in the SkipList. Complexity **O(1)**.

**key()**: Returns null since there is no associated key in the SkipList. Complexity **O(1)**.

**next()**: Moves the current position pointer to the next node in the SkipList. Complexity **O(1)**.

**rewind()**: Resets the current position pointer to the first node in the SkipList. Complexity **O(1)**.

**valid()**: Returns true if the current position pointer is not null and false otherwise. Complexity **O(1)**.


### ArrayAccess methods:
**offsetExists()**: Accepts an offset parameter and returns true if the value associated with the offset exists in the SkipList and false otherwise. Complexity **O(n)**.

**offsetGet()**: Accepts an offset parameter and returns the value associated with the offset in the SkipList. Complexity **O(n)**.

**offsetSet()**: Accepts an offset and a value parameter and adds the value to the SkipList. (see Exceptions). Complexity **O(n)**.

**offsetUnset()**: Accepts an offset parameter and removes the value associated with the offset from the SkipList. Complexity **O(n)**.


### Main methods:
**getRandom()**: Returns a random value from the SkipList. Complexity **O(n)**.

**getRandomM()**: Accepts a number parameter and returns an array of "number" random values from the SkipList. Complexity **O(n)**.

**randomLevel()**: Returns a random level between 0 and the maximum level. Complexity **O(maxLevel)**.

**count()**: Returns the number of elements in the SkipList. Complexity **O(1)**.

**addMany()**: Accepts an array parameter and adds all its values to the SkipList. Complexity **O(m log n)**.

**toArray()**: Returns an array of all the values in the SkipList. Complexity **O(n)**.

**search()**: Accepts a value parameter and searches for its position in the SkipList. Complexity **O(log n)**.

**add()**: Accepts a value parameter and adds it to the SkipList. Complexity **O(log n)**.

**delete()**: Accepts a value parameter and removes it from the SkipList. Complexity **O(log n)**.

**printList()**: Print the skip list without considering spacing. Just a raw print of all levels.
