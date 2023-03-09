<?php
/**
 * Represents a node in the SkipList
 */
class SkipNode
{
    /** @var mixed $val Value stored in the node */
    public $val;

    /** @var array|null $forward Array of pointers to nodes of same level */
    public $forward;

    /**
     * Constructor method
     *
     * @param mixed|null $val The value to be stored in the node
     * @param int $level The level of the node (default 0)
     */
    public function __construct($val = null, $level = 0)
    {
        $this->val = $val;
        $this->forward = array_fill(0, $level + 1, null);
    }
}

/**
 * Represents a SkipList
 */
class SkipList implements Iterator, ArrayAccess, Countable, Serializable
{
    /** @var int $maxLevel The maximum level of the SkipList */
    private $maxLevel;

    /** @var int $level The current level of the SkipList */
    private $level;

    /** @var SkipNode $head The head node of the SkipList */
    private $head;

    /** @var SkipNode $position The current node during iteration */
    private $position;

    /** @var int $number_elements The number of elements in the SkipList */
    private $number_elements;

    /** @var callable|null $comparator The comparison function used for sorting */
    private $comparator;

    /**
     * Sets the comparator function used for sorting
     *
     * @param callable|null $comparator The comparison function used for sorting
     */
    public function setComparator($comparator)
    {
        $this->comparator = $comparator;
        if ($this->comparator === null) {
            $this->comparator = function ($a, $b, $op) {
                switch ($op) {
                    case "<":
                        return $a < $b;
                        break;
                    case "===":
                        return $a === $b;
                        break;
                }
            };
        }
    }

    /**
     * Constructor method
     *
     * @param int $maxLevel The maximum level of the SkipList (default 10)
     * @param callable|null $comparator The comparison function used for sorting (default null)
     * @throws ErrorException If the maximum level is less than 1
     */
    public function __construct($maxLevel = 10, $comparator = null)
    {
        if ($maxLevel < 1) {
            throw new ErrorException("Levels must be greater than 0");
        }

        $this->maxLevel = $maxLevel;
        $this->level = 0;
        $this->head = new SkipNode(null, $this->maxLevel);
        $this->position = $this->head;
        $this->number_elements = 0;

        $this->setComparator($comparator);
    }

    /**
     * Magic method to call closure as object method
     *
     * @param string $method The name of the method
     * @param array $args The arguments passed to the method
     * @return mixed The result of the method
     */
    public function __call($method, $args)
    {
        if ($this->{$method} instanceof Closure) {
            return call_user_func_array($this->{$method}, $args);
        }
    }

    /**
     * Returns the current value during iteration
     *
     * @return mixed The current value
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->position->val;
    }

    /**
     * Returns the current key during iteration
     *
     * @return null
     */
    public function key()
    {
        return null;
    }

    /**
     * Moves the iterator to the next element
     *
     * @return void
     */
    public function next(): void
    {
        $this->position = $this->position->forward[0];
    }

    /**
     * Rewinds the iterator to the first element
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->position = $this->head->forward[0];
    }

    /**
     * Checks if the current position of the iterator is valid
     *
     * @return bool True if the position is valid, false otherwise
     */
    public function valid(): bool
    {
        return $this->position !== null;
    }

    /**
     * Checks if an offset exists
     *
     * @param mixed $offset The offset to check
     * @return bool True if the offset exists, false otherwise
     */
    public function offsetExists($offset): bool
    {
        return $this->offsetGet($offset) !== null;
    }

    /**
     * Returns the value at the specified offset
     *
     * @param mixed $offset The offset to retrieve the value from
     * @return mixed|null The value at the specified offset, or null if the offset is out of bounds
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        $count = $this->number_elements;
        if ($offset >= $count) {
            return null;
        }

        $maxLevel = $this->level;
        $current = $this->head->forward[0];
        $count = 0;

        while ($current !== null) {
            if ($count === $offset) {
                return $current->val;
                break;
            }

            $count++;
            $current = $current->forward[0];
        }

        return null;
    }

    /**
     * Sets the value at the specified offset
     *
     * @param mixed $offset The offset to set the value at
     * @param mixed $value The value to set at the specified offset
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->offsetUnset($offset);
        $this->add($value);
    }

    /**
     * Unsets the value at the specified offset
     *
     * @param mixed $offset The offset to unset the value at
     * @return void
     */
    public function offsetUnset($offset): void
    {
        $val = $this->offsetGet($offset);
        if ($val !== null) {
            $this->delete($val);
        }
    }

    /**
     * Returns a random element from the SkipList
     *
     * @return mixed|null A random element from the SkipList, or null if the SkipList is empty
     */
    public function getRandom()
    {
        $maxLevel = $this->level;
        $current = $this->head->forward[0];
        $count = $this->number_elements;

        $randomIndex = mt_rand(1, $count);
        $current = $this->head->forward[0];
        $count = 0;

        while ($current !== null) {
            $count++;

            if ($count === $randomIndex) {
                return $current->val;
            }

            $current = $current->forward[0];
        }

        return null;
    }

    /**
     * Returns an array of random elements from the SkipList
     *
     * @param int $number The number of random elements to return
     * @return array An array of random elements from the SkipList
     */
    public function getRandomM($number)
    {
        $count = $this->number_elements;

        $indexes = [];
        for ($i = 0; $i < $number; $i++) {
            $r = mt_rand(1, $count);
            array_push($indexes, mt_rand(1, $count));
        }
        sort($indexes);

        $maxLevel = $this->level;
        $current = $this->head->forward[0];
        $count = 1;
        $indexes_position = 0;

        $randomValues = [];

        while ($current !== null && $indexes_position < $number) {
            if ($count === $indexes[$indexes_position]) {
                array_push($randomValues, $current->val);
                $indexes_position++;
            } else {
                $count++;
                $current = $current->forward[0];
            }
        }

        return $randomValues;
    }

    /**
     * Generates a random level for a new node in the SkipList
     *
     * @return int The level of the new node
     */
    private function randomLevel()
    {
        $level = 0;

        while (mt_rand(0, 1) && $level < $this->maxLevel) {
            $level++;
        }

        return $level;
    }

    /**
     * Returns the number of elements in the SkipList
     *
     * @return int The number of elements in the SkipList
     */
    public function count()
    {
        return $this->number_elements;
    }

    /**
     * Adds multiple values to the SkipList
     *
     * @param array $arr An array of values to add to the SkipList
     * @return void
     */
    public function addMany(&$arr)
    {
        foreach ($arr as $val) {
            $this->add($val);
        }
    }

    /**
     * Returns an array representation of the SkipList
     *
     * @return array An array representation of the SkipList
     */
    public function toArray()
    {
        $result = [];
        $current = $this->head->forward[0];

        while ($current !== null) {
            $result[] = $current->val;
            $current = $current->forward[0];
        }

        return $result;
    }

    /**
     * Searches for a value in the SkipList. SkipNode returned.
     *
     * @param mixed $val The value to search for
     * @return SkipNode|null The SkipNode containing the value, or null if the value is not in the SkipList
     */
    public function search($val)
    {
        $current = $this->head;

        for ($i = $this->level; $i >= 0; $i--) {
            while (
                $current->forward[$i] !== null &&
                $this->comparator($current->forward[$i]->val, $val, "<")
            ) {
                $current = $current->forward[$i];
            }
        }

        $current = $current->forward[0];

        if (
            $current !== null &&
            $this->comparator($current->val, $val, "===")
        ) {
            return $current;
        } else {
            return null;
        }
    }

    /**
     * Searches for values in a range in the SkipList
     *
     * @param mixed $val The starting value of the range
     * @param mixed $valEnd The ending value of the range
     * @return array An array of values in the range
     */
    public function searchRange($val, $valEnd)
    {
        $current = $this->head;

        for ($i = $this->level; $i >= 0; $i--) {
            while (
                $current->forward[$i] !== null &&
                $this->comparator($current->forward[$i]->val, $val, "<")
            ) {
                $current = $current->forward[$i];
            }
        }

        $current = $current->forward[0];

        $toReturn = [];

        while (
            $current !== null &&
            ($this->comparator($current->val, $valEnd, "<") ||
                $this->comparator($current->val, $valEnd, "==="))
        ) {
            $toReturn[] = $current->val;
            $current = $current->forward[0];
        }

        return $toReturn;
    }

    /**
     * Adds a value to the SkipList if not present
     *
     * @param mixed $val The value to add to the SkipList
     * @return SkipNode The SkipNode containing the added value or the SkipNode already present with the value specified
     */
    public function add($val)
    {
        $update = array_fill(0, $this->maxLevel, null);
        $current = $this->head;

        for ($i = $this->level; $i >= 0; $i--) {
            while (
                $current->forward[$i] !== null &&
                $this->comparator($current->forward[$i]->val, $val, "<")
            ) {
                $current = $current->forward[$i];
            }

            $update[$i] = $current;
        }

        $current = $current->forward[0];

        if (
            $current === null ||
            !$this->comparator($current->val, $val, "===")
        ) {
            $newLevel = $this->randomLevel();

            if ($newLevel > $this->level) {
                for ($i = $this->level + 1; $i <= $newLevel; $i++) {
                    $update[$i] = $this->head;
                }

                $this->level = $newLevel;
            }

            $node = new SkipNode($val, $newLevel);

            for ($i = 0; $i <= $newLevel; $i++) {
                $node->forward[$i] = $update[$i]->forward[$i];
                $update[$i]->forward[$i] = $node;
            }

            $this->number_elements++;
        } else {
            $node = $current;
        }

        return $node;
    }

    /**
     * Deletes a value from the SkipList
     *
     * @param mixed $val The value to delete from the SkipList
     * @return bool true if the value was successfully deleted, false otherwise
     */
    public function delete($val)
    {
        $update = array_fill(0, $this->maxLevel, null);
        $current = $this->head;

        for ($i = $this->level; $i >= 0; $i--) {
            while (
                $current->forward[$i] !== null &&
                $this->comparator($current->forward[$i]->val, $val, "<")
            ) {
                $current = $current->forward[$i];
            }

            $update[$i] = $current;
        }

        $current = $current->forward[0];

        if (
            $current !== null &&
            $this->comparator($current->val, $val, "===")
        ) {
            for ($i = 0; $i <= $this->level; $i++) {
                if ($update[$i]->forward[$i] === $current) {
                    $update[$i]->forward[$i] = $current->forward[$i];
                }
            }

            while (
                $this->level > 0 &&
                $this->head->forward[$this->level] === null
            ) {
                $this->level--;
            }

            $this->number_elements--;

            return true;
        }

        return false;
    }

    /**
     * Prints the values in the SkipList, separated by levels
     *
     * @param Closure $string_converter A function to convert each value to a string
     */
    public function printList($string_converter = null)
    {
        if ($string_converter === null) {
            $string_converter = function ($a) {
                return $a;
            };
        }

        for ($i = $this->maxLevel; $i >= 0; $i--) {
            $current = $this->head->forward[$i];

            print $i . ":\t";
            while ($current !== null) {
                print $string_converter($current->val) . " ";
                $current = $current->forward[$i];
            }

            print "\n";
        }
    }

    /**
     * Serializes the SkipList except the Closures
     *
     * @return string The serialized representation of the SkipList
     */
    public function serialize(): string
    {
        return serialize([
            "maxLevel" => $this->maxLevel,
            "level" => $this->level,
            "head" => $this->head,
            "position" => $this->position,
            "number_elements" => $this->number_elements,
        ]);
    }

    /**
     * Unserializes the SkipList except the Closures
     *
     * @param string $data The serialized representation of the SkipList
     */
    public function unserialize($data): void
    {
        $vars = unserialize($data);
        $this->maxLevel = $vars["maxLevel"];
        $this->level = $vars["level"];
        $this->head = $vars["head"];
        $this->position = $vars["position"];
        $this->number_elements = $vars["number_elements"];
        $this->setComparator(null);
    }
}
