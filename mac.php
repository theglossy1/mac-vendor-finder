<!DOCTYPE html>
<html><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/mac.css" type="text/css" media="all">
<title>MAC Address Vendor Finder</title>
<script src='//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js'></script>
<script src='//cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.28.3/js/jquery.tablesorter.min.js'></script>
<style>
th { cursor: pointer }
i { color:red }
</style>
</head>

<body>

<?php
if (!isset($_POST['addresses'])) {
 echo '
 <h2>Vendor MAC Address Getter</h2>
 <h4>Enter ARP or MAC address output</h4>
 <p>Hint: on a Cisco router/switch, use <i>show ip arp</i> or <i>show mac address</i> output; on an ASA, use <i>show arp</i>
 <br><span style="margin-left:1em">(or just paste a list of MAC addresses embedded wherever you want within the line).</span></p>
 <form method="post" action="/mac">
 <textarea cols="80" rows="12" name="addresses"></textarea><br>
 <input type="submit"></input></form></body></html>';
 exit;
} else {
 $address_list = trim($_POST['addresses']);
 $address_array = preg_split("/\R/",$address_list);
 echo "<table>
<thead>
<tr>
 <th id='ip'>IP Address <span>&#8597;</span></th>
 <th id='mac'>MAC Address <span>&#8597;</span></th>
 <th id='vendor'>Vendor <span>&#8597;</span></th>
</tr>
</thead>\n";
 foreach($address_array as $line) {
  if (! strpos($line,"INCOMPLETE")) {
   preg_match("/\b(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\b.*\b((?:[0-9a-f]{2}[-:.]?){5}[0-9a-f]{2})\b/",$line,$parts);
   if (!isset($parts[2])) {
    if (preg_match("/\b((?:[0-9a-f]{2}[-:.]?){5}[0-9a-f]{2})\b/i",$line,$mac_part)) {
     $mac_addr = $mac_part[1];
     echo "<tr><td> n/a </td>";
     echo "<td>$mac_addr</td><td>" . rtrim(find_vendor($mac_addr),".") . "</td></tr>\n";
    }
   } else if (filter_var($parts[1],FILTER_VALIDATE_IP) and preg_match("/[0-9a-f]{12}/i",mac_normalize($parts[2]))) {
    echo "<tr><td>$parts[1]</td>";
    echo "<td>$parts[2]</td><td>" . rtrim(find_vendor($parts[2]),".") . "</td></tr>\n";
   }
  }
 }
 echo "</table>";
}


function find_vendor ($mac) {
 if (file_exists("oui.txt"))
   $mac_db = file_get_contents("oui.txt");
 else
   return "vendor database missing from server";

 $vendor = substr(strtoupper(preg_replace("/[-:.]/","",$mac)),0,6);
 preg_match("/^$vendor(.*)/m",$mac_db,$matches);
 if (isset($matches[0])) {
  return trim(substr($matches[0],20,50));
 } else {
  return "$vendor: unknown vendor";
 }
}

function mac_normalize($mac_addr) {
 $mac_array = preg_split("/[-:.]/",$mac_addr);
 $mac = "";
 foreach ($mac_array as $mac_bit) {
  $mac .= str_pad($mac_bit,2,"0",STR_PAD_LEFT);
 }
 return $mac;
}

?>
<script>
$(document).ready(function() {
 $("table").tablesorter();
 $("th#ip").click    (function() { $("th").removeClass("selected"); $(this).addClass("selected"); });
 $("th#mac").click   (function() { $("th").removeClass("selected"); $(this).addClass("selected"); });
 $("th#vendor").click(function() { $("th").removeClass("selected"); $(this).addClass("selected"); });
});
</script>
</body></html>
