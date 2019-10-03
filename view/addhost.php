<form action="index.php?act=doaddhost" method="POST">
    <table>
        <tr>
            <td>Host Address</td>
            <td><input type="text" name="host_address" id="host_address"></td>
        </tr>
        <tr>
            <td>Host IP Address</td>
            <td><input type="text" name="host_ipaddress" id="host_ipaddress"></td>
        </tr>
        <tr>
            <td>Host Port</td>
            <td><input type="text" name="host_port" id="host_port" value="8443"></td>
        </tr>
        <tr>
            <td>Host Path</td>
            <td><input type="text" name="host_path" id="host_path" value="enterprise/control/agent.php"</td>
        </tr>
        <tr>
            <td>Host User</td>
            <td><input type="text" name="host_user" id="host_user" value="admin"></td>
        </tr>
        <tr>
            <td>Host Wachtwoord</td>
            <td><input type="password" name="host_pass" id="host_pass"></td>
        </tr>
        <tr>
            <td>Host Wachtwoord (opnieuw)</td>
            <td><input type="password" name="host_pass_confirm" id="host_pass_confirm"></td>
        </tr>
        <tr>
            <td>Plesk Versie</td>
            <td>
                <select name="host_version" id="host_version">
					<option value="1.4.1.0">Plesk 8.0 (Linux) / Plesk 7.6 (Window)</option>
					<option value="1.4.2.0">Plesk 8.1 (Linux en Windows)</option>
                    <option value="1.5.0.0">Plesk 8.1.1 (Linux en Windows)</option>
                    <option value="1.5.1.0">Plesk 8.2 (Linux en Windows)</option>
                    <option value="1.5.2.0">Plesk 8.3 (Linux en Windows)</option>
                    <option value="1.5.2.1">Plesk 8.4/8.6 (Linux en Windows)</option>
                    <option value="1.6.0.0">Plesk 9.0 (Linux en Windows)</option>
				</select>
            </td>
        </tr>
        <tr>
            <td>Gebruik Secret Key (extra beveiliging)</td>
            <td><input type="checkbox" name="host_authmethod" id="host_authmethod" value="1" checked></td>
        </tr>
        <tr>
			<td>&nbsp;</td>
			<td><input type='submit' value='Toevoegen'></td>
		</tr>
    </table>
</form>