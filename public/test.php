<?php
function check_a()
{
    return 'A';
}
function check_b()
{
    return 'B';
}
function check_c()
{
    return 'C';
}

if(($a = check_a()) && ($b = check_b()))
{
    var_dump($a);
    var_dump($b);
    var_dump($c);
}
