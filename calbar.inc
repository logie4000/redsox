<table width="100%" cellspacing="0" id="navbar">
<tr><td>&nbsp;</td>
<?php
for ($m = 4; $m < 11; $m++) {
        $month = getMonthName($m);
        printf("<td><a class='navitem' href='#%s'>%s</a></td>\n", $month, $month);
}

?>
<td>&nbsp;</td></tr>
</table>

