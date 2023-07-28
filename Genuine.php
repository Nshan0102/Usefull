<?php

/**
 * This class solves the problem of moving characters from left to right and right to left.
 * You have an array where there are 3 types of elements - An empty cell and two other characters.
 * The empty cell must be placed in the middle of the array and other two characters must be grouped
 * in the left and right sides of an empty cell: Example: ["a", "a", "a", "[]", "b", "b", "b"]
 * In this example Char1 is <a>, Char2 is <b>, and the EmptyCell is <[]>
 * There are some rules while moving elements
 * 1. The characters on the left side of an empty cell must always move to right
 *    Example: ["a", "a", "a", "[]", "b", "b", "b"] => ["a", "a", "[]", "a", "b", "b", "b"]
 * 2. The characters on the right side of an empty cell must always move to left
 *    Example: ["a", "a", "[]", "a", "b", "b", "b"] => ["a", "a", "b", "a", "[]", "b", "b"]
 * 3. The characters can swap only with an EmptyCell, Which means Two Chars are not able to swap together
 *    Example: ["a", "a", "[]", "a", "b", "b", "b"] => ["a", "a", "[]", "b", "a", "b", "b"]
 * 4. If the distance between the Character and EmptyCell is more than one cell you cannot swap them
 *    Example: ["a", "[]", "a", "a", "b", "b", "b"] => ["a", "b", "a", "a", "[]", "b", "b"]
 * 5. The characters are not able to jump over the same character
 *    Example: ["a", "a", "a", "[]", "b", "b", "b"] => ["a", "[]", "a", "a", "b", "b", "b"]
 * 6. The character can jump over other character.
 *    Example: ["a", "a", "[]", "a", "b", "b", "b"] => ["a", "a", "b", "a", "[]", "b", "b"]
 *
 * Example of solving -> ["a", "a", "[]", "b", "b"]
 * step 1: ["a","[]","a","b","b"]
 * step 2: ["a","b","a","[]","b"]
 * step 3: ["a","b","a","b","[]"]
 * step 4: ["a","b","[]","b","a"]
 * step 5: ["[]","b","a","b","a"]
 * step 6: ["b","[]","a","b","a"]
 * step 7: ["b","b","a","[]","a"]
 * step 8: ["b","b","[]","a","a"]
 */
class Genuine
{
    private array $result;
    private int $steps;

    public function __construct(private bool $showSteps = false)
    {
    }

    private function move(
        array  $arr,
        string $letter,
        int    $count,
        string $emptySign,
        string $charactersInTheLeft,
        string $charactersInTheRight
    ): array
    {
        $steps = 0;
        while ($steps < $count) {
            $index = array_search($emptySign, $arr);
            if ($letter === $charactersInTheLeft) {
                if ($arr[$index - 1] === $charactersInTheLeft) {
                    $arr[$index - 1] = $emptySign;
                    $arr[$index] = $charactersInTheLeft;
                } else if ($arr[$index - 2] = $charactersInTheLeft) {
                    $arr[$index - 2] = $emptySign;
                    $arr[$index] = $charactersInTheLeft;
                }
            }
            if ($letter === $charactersInTheRight) {
                if ($arr[$index + 1] === $charactersInTheRight) {
                    $arr[$index + 1] = $emptySign;
                    $arr[$index] = $charactersInTheRight;
                } elseif ($arr[$index + 2] === $charactersInTheRight) {
                    $arr[$index + 2] = $emptySign;
                    $arr[$index] = $charactersInTheRight;
                }
            }
            $steps++;
            if ($this->showSteps) {
                echo json_encode($arr) . "\n";
            }
        }
        return ["arr" => $arr, "steps" => $steps];
    }

    private function isValidInput(
        array  $arr,
        string $emptySign,
        string $charactersInTheLeft,
        string $charactersInTheRight
    ): bool
    {
        $countOf_Empty = 0;
        $countOf_X = 0;
        $countOf_O = 0;

        foreach ($arr as $element) {
            if ($element === $emptySign) {
                $countOf_Empty++;
            } elseif ($element === $charactersInTheLeft) {
                $countOf_X++;
            } elseif ($element === $charactersInTheRight) {
                $countOf_O++;
            }
        }

        if ($countOf_Empty !== 1 || $countOf_X !== $countOf_O) {
            return false;
        }
        return true;
    }

    public function solve(array $arr): void
    {
        echo "Input: " . json_encode($arr) . "\n";
        $characters = array_values(array_unique($arr));
        if (count($characters) !== 3) {
            echo "ERROR: Invalid input array \n";
            return;
        }
        $charactersInTheLeft = $characters[0];
        $emptySign = $characters[1];
        $charactersInTheRight = $characters[2];
        if (!$this->isValidInput($arr, $emptySign, $charactersInTheLeft, $charactersInTheRight)) {
            echo "ERROR: Invalid input array \n";
            return;
        }
        $countOfElements = array_count_values($arr);
        $count = $countOfElements[$charactersInTheLeft];
        $letter = $charactersInTheLeft;
        $steps = 0;
        for ($i = 1; $i < $count; $i++) {
            $res = $this->move($arr, $letter, $i, $emptySign, $charactersInTheLeft, $charactersInTheRight);
            $arr = $res["arr"];
            $steps += $res["steps"];
            $letter = $letter === $charactersInTheLeft ? $charactersInTheRight : $charactersInTheLeft;
        }

        for ($j = 1; $j <= 3; $j++) {
            $res = $this->move($arr, $letter, $count, $emptySign, $charactersInTheLeft, $charactersInTheRight);
            $arr = $res["arr"];
            $steps += $res["steps"];
            $letter = $letter === $charactersInTheLeft ? $charactersInTheRight : $charactersInTheLeft;
        }

        for ($i = $count - 1; $i >= 1; $i--) {
            $res = $this->move($arr, $letter, $i, $emptySign, $charactersInTheLeft, $charactersInTheRight);
            $arr = $res["arr"];
            $steps += $res["steps"];
            $letter = $letter === $charactersInTheLeft ? $charactersInTheRight : $charactersInTheLeft;
        }
        $this->result = $arr;
        $this->steps = $steps;
        $this->showResult();
    }

    private function showResult(): void
    {
        echo "Result: " . json_encode($this->result) . "\n";
        echo "Steps: " . $this->steps . "\n";
    }
}

$genuine = new Genuine(true);
$genuine->solve(["z", "z", "[]", "y", "y"]);
