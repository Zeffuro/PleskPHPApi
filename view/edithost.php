<?php
$hosts = new Hosts();
$host = $_POST['host'];
$hostinfo = $hosts->gethost($host);
?>
<form action="index.php?act=doedithost" method="POST">
    <table>
        <input type="hidden" name="host_id" id="host_id" value="<?=$hostinfo['host_id'] ?>">
        <tr>
            <td>Host Address</td>
            <td><input type="text" name="host_address" id="host_address" value="<?=$hostinfo['host_address'] ?>"></td>
        </tr>
        <tr>
            <td>Host IP Address</td>
            <td><input type="text" name="host_ipaddress" id="host_ipaddress" value="<?=$hostinfo['host_ipaddress'] ?>"></td>
        </tr>
        <tr>
            <td>Host Port</td>
            <td><input type="text" name="host_port" id="host_port" value="<?=$hostinfo['host_port'] ?>"></td>
        </tr>
        <tr>
            <td>Host Path</td>
            <td><input type="text" name="host_path" id="host_path" value="<?=$hostinfo['host_path'] ?>"></td>
        </tr>
        <tr>
            <td>Plesk Versie</td>
            <td>
                <select name="host_version" id="host_version">               
					<option value="1.4.1.0" <?php if($hostinfo['host_version'] == "1.4.1.0"){?> selected <?php } ?>>Plesk 8.0 (Linux) / Plesk 7.6 (Window)</option>
					<option value="1.4.2.0" <?php if($hostinfo['host_version'] == "1.4.2.0"){?> selected <?php } ?>>Plesk 8.1 (Linux en Windows)</option>
                    <option value="1.5.0.0" <?php if($hostinfo['host_version'] == "1.5.0.0"){?> selected <?php } ?>>Plesk 8.1.1 (Linux en Windows)</option>
                    <option value="1.5.1.0" <?php if($hostinfo['host_version'] == "1.5.1.0"){?> selected <?php } ?>>Plesk 8.2 (Linux en Windows)</option>
                    <option value="1.5.2.0" <?php if($hostinfo['host_version'] == "1.5.2.0"){?> selected <?php } ?>>Plesk 8.3 (Linux en Windows)</option>
                    <option value="1.5.2.1" <?php if($hostinfo['host_version'] == "1.5.2.1"){?> selected <?php } ?>>Plesk 8.4/8.6 (Linux en Windows)</option>
                    <option value="1.6.0.0" <?php if($hostinfo['host_version'] == "1.6.0.0"){?> selected <?php } ?>>Plesk 9.0 (Linux en Windows)</option>
				</select>
            </td>
        </tr>
        <tr>
			<td>&nbsp;</td>
			<td><input type='submit' value='Aanpassen'></td>
		</tr>
    </table>
</form>