<?php
class Benchmark
{
    public static $points = array();

    public static function start($name)
    {
        static $counter = 0;

        $token = base_convert($counter++, 10, 32);

        Benchmark::$points[$token] = array(
            'name' => (string) $name,
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(),
            'end_time' => FALSE,
            'end_memory' => FALSE
        );

        return $token;
    }

    public static function stop($token)
    {
        Benchmark::$points[$token]['end_time'] = microtime(true);
        Benchmark::$points[$token]['end_memory'] = memory_get_usage();

        $point = Benchmark::$points[$token];

        return array(
            $point['name'],
            $point['end_time'] - $point['start_time'],
            $point['end_memory'] - $point['start_memory']
        );
    }

    public static function statistics()
    {
        $stat = array();

        if (Benchmark::$points) {
            foreach (Benchmark::$points as $token => $vals) {
                if ($vals['end_time'] === FALSE OR $vals['end_memory'] === FALSE) Benchmark::stop($token);

                $stat[$vals['name']] = array(
                    'time' => $vals['end_time'] - $vals['start_time'],
                    'mem' => $vals['end_memory'] - $vals['start_memory'],
                    'token' => $token
                );
            }
        }

        return $stat;
    }
}