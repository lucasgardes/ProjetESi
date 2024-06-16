<?php
class Bicycle {
    public $id;
    public $autonomy = 50;
    public $loadCapacity = 200;
    public $currentLoad = 0;
    public $position;
    public $picking = false;

    public $baseLocation = 'Porte d\'Ivry';
    
    public function __construct($id, $position) {
        $this->id = $id;
        $this->position = $position;
    }

    public function updatePosition($newPosition) {
        $this->position = $newPosition;
    }

    public function calculateAutonomyLoss($distance, $intersections, $isWinter = false) {
        $lossPerIntersection = 1 / 20;
        $this->autonomy -= $distance + ($intersections * $lossPerIntersection);
        if ($isWinter) {
            $this->autonomy *= 0.90;
        }
    }

    public function updateLoad() {
        $this->currentLoad += 50;
        if ($this->currentLoad >= $this->loadCapacity) {
            $this->returnToBase();
            $this->currentLoad = 0;
        }
    }

    public function determinPathToDestination($destination, $graph) {
        if ($this->position == $destination) {
            return [$this->position];
        }

        $queue = new SplQueue();
        $queue->enqueue([$this->position, [$this->position]]);
        $visited = [$this->position => true];

        while (!$queue->isEmpty()) {
            list($currentStop, $path) = $queue->dequeue();

            if (isset($graph[$currentStop])) {
                foreach ($graph[$currentStop] as $neighbor) {
                    if (!isset($visited[$neighbor])) {
                        $visited[$neighbor] = true;
                        $newPath = array_merge($path, [$neighbor]);

                        if ($neighbor == $destination) {
                            return $newPath;
                        }

                        $queue->enqueue([$neighbor, $newPath]);
                    }
                }
            }
        }

        return [];
    }

    public function moveToDestination($destination, $graph, $picking = false) {
        if ($picking) {
            $this->picking = true;
        } else {
            $this->picking = false;
        }
        $path = $this->determinPathToDestination($destination, $graph);
        $i = 0;
        do {
            $this->moveToNextStop($graph, $path[$i]);
            $i++;
        } while ($this->position != $destination);
    }

    public function returnToBase() {
        $this->position = $this->baseLocation;
        $this->autonomy = 50;
        $this->currentLoad = 0;
    }

    public function moveToNextStop($graph, $nextStop) {
        $this->autonomy -= 0.5;
        $stopsToBase = $this->calculateStopsToBase($graph, $this->position, $this->baseLocation);

        $requiredAutonomy = $stopsToBase * 0.5;

        if ($this->autonomy >= $requiredAutonomy) {
            $this->updatePosition($nextStop);
            if ($this->picking) {
                $this->updateLoad();
            }
        } else {
            $this->returnToBase();
        }
    }

    function calculateStopsToBase($graph, $currentStop, $baseStop) {
        if ($currentStop == $baseStop) {
            return 0;
        }
    
        $visited = [];
        $queue = new SplQueue();
    
        
        $queue->enqueue([$currentStop, 0]);
        $visited[$currentStop] = true;
    
        while (!$queue->isEmpty()) {
            list($stop, $depth) = $queue->dequeue();
    
            if (isset($graph[$stop])) {
                foreach ($graph[$stop] as $neighbor) {
                    if (!isset($visited[$neighbor])) {
                        if ($neighbor == $baseStop) {
                            return $depth + 1;
                        }
                        $queue->enqueue([$neighbor, $depth + 1]);
                        $visited[$neighbor] = true;
                    }
                }
            }
        }
    
        return PHP_INT_MAX;
    }
}
?>