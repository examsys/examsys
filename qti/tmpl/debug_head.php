<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author Adam Clarke
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */
require_once '../include/staff_auth.inc';
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
	<title>Rog&#333; Import to QTI</title>

	<script type="text/javascript" src="./qti/js/mootools-1.2.4.js"></script>

  <link rel="stylesheet" type="text/css" href="./css/body.css" />
	<style type="text/css">
		.divider {font-size:80%; padding-left:16px; padding-bottom:2px; font-weight:bold}
		a {color:black}
		a:hover {color:blue}
		.f {float:left; width:375px; padding-left:12px; font-size:80%}
		.recent {color:blue; font-size:90%}
		.param_section {margin:16px;padding:6px;border: 1px solid #dddddd;}

	.exp_table {
		border-left: 1px solid #dddddd;
		border-top: 1px solid #dddddd;
	}

	.exp_table tr td,.exp_table tr th {
		border-bottom: 1px solid #dddddd;
		border-right: 1px solid #dddddd;
		padding: 1px;
		font-size:80%;
	}
	
	.paper_head {
		font-size:140%;
	}
	
	.screen_head {
		font-size:120%;
	}

  .print_cont {
    /*border: 1px solid #CCCCCC;*/
    margin:6px;
    background-color:#EEFFEE;
  }

  .print_head {
    /*font-size:100%;*/
    padding: 4px;
  }

  .print_body {
    display: none;
    /*border-top: 1px solid #CCCCCC;*/
  }

  .print_label {
    font-weight: bold;
    /*font-size:80%;*/
  }

  .print_variable {
    /*font-size:80%;*/
  }

  .print_raw {
    font-size:85%;
  }

  .print_raw pre {
    background-color:#FFEEEE;
    padding:6px;
  }
</style>

<script type="text/javascript">

function print_nice_expand(id)
{
	$('print_nice_' + id).style.display = 'inline';
	
}

function print_nice_contract(id)
{
	$('print_nice_' + id).style.display = 'none';
}

function print_nice_expand_all(id)
{
	print_nice_expand(id)
}

function print_nice_toggle_raw(id)
{
	if ($('print_nice_raw_' + id).style.display == 'none')
	{
		$('print_nice_raw_' + id).style.display = 'inline';
	} else {
		$('print_nice_raw_' + id).style.display = 'none';
	}
}
</script>
</head>

<body>
