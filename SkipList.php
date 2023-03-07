<?php
class SkipNode
{
    public $val;
    public $forward;

    public function __construct($val = null, $level = 0)
    {
        $this->val = $val;
        $this->forward = array_fill(0, $level + 1, null);
    }
}

class SkipList implements Iterator, ArrayAccess, Countable
{
    private $maxLevel;
    private $level;
    private $head;
    private $position;
    private $number_elements;
    private $comparator;

    public function __construct($maxLevel = 10, $comparator = null)
    {
        if ($maxLevel < 1) {
            throw new ErrorException("Levels must be greater than 0");
        }

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

        $this->maxLevel = $maxLevel;
        $this->level = 0;
        $this->head = new SkipNode(null, $this->maxLevel);
        $this->position = $this->head;
        $this->number_elements = 0;
    }

    public function __call($method, $args)
    {
        if ($this->{$method} instanceof Closure) {
            return call_user_func_array($this->{$method}, $args);
        }
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->position->val;
    }

    public function key()
    {
        return null;
    }

    public function next(): void
    {
        $this->position = $this->position->forward[0];
    }

    public function rewind(): void
    {
        $this->position = $this->head->forward[0];
    }

    public function valid(): bool
    {
        return $this->position !== null;
    }

    public function offsetExists($offset): bool
    {
        return $this->offsetGet($offset) !== null;
    }

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

    public function offsetSet($offset, $value): void
    {
        $this->offsetUnset($offset);
        $this->add($value);
    }

    public function offsetUnset($offset): void
    {
        $val = $this->offsetGet($offset);
        if ($val !== null) {
            $this->delete($val);
        }
    }

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

    private function randomLevel()
    {
        $level = 0;

        while (mt_rand(0, 1) && $level < $this->maxLevel) {
            $level++;
        }

        return $level;
    }

    public function count()
    {
        return $this->number_elements;
    }

    public function addMany(&$arr)
    {
        foreach ($arr as $val) {
            $this->add($val);
        }
    }

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

    public function add($val)
    {
        $this->number_elements++;
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
        }
    }

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
            $this->number_elements--;

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

            return true;
        }

        return false;
    }

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
}
