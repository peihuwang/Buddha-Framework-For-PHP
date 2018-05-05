<?php




if (!function_exists('checkdnsrr')) {
    function checkdnsrr($host, $type) {
        if(!empty($host) && !empty($type)) {
            @exec('nslookup -type=' . escapeshellarg($type) . ' ' . escapeshellarg($host), $output);
            foreach ($output as $k => $line) {
                if(preg_match('/^/' . $host, $line)) {
                    return true;
                }
            }
        }
        return false;
    }
}

// getmxrr() support for Windows by HM2K <php [spat] hm2k.org>
function win_getmxrr($hostname, &$mxhosts, &$mxweight=false) {
    if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') return;
    if (!is_array ($mxhosts) ) $mxhosts = array();
    if (empty($hostname)) return;
    $exec='nslookup -type=MX '.escapeshellarg($hostname);
    @exec($exec, $output);
    if (empty($output)) return;
    $i=-1;
    foreach ($output as $line) {
        $i++;
        if (preg_match("/^$hostname	MX preference = ([0-9]+), mail exchanger = (.+)$/i", $line, $parts)) {
            $mxweight[$i] = trim($parts[1]);
            $mxhosts[$i] = trim($parts[2]);
        }
        if (preg_match('/responsible mail addr = (.+)$/i', $line, $parts)) {
            $mxweight[$i] = $i;
            $mxhosts[$i] = trim($parts[1]);
        }
    }
    return ($i!=-1);
}

// Define
if (!function_exists('getmxrr')) {
    function getmxrr($hostname, &$mxhosts, &$mxweight=false) {
        return win_getmxrr($hostname, $mxhosts, $mxweight);
    }
}

?>