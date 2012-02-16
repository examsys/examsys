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

require '../../include/staff_auth.inc';

?>
<script>
function local_LoadPanel(type)
{
    var url = load_source + '/load_' + type + '.php';
	$('local_load_panel').load(url);
}
</script>

<button onclick="local_LoadPanel('question');">Question</button>
<button onclick="local_LoadPanel('batch_q');">Batch Question</button>
<button onclick="local_LoadPanel('paper');">Paper</button>
<button onclick="local_LoadPanel('batch_p');">Batch Paper</button>

<div id="local_load_panel"></div>