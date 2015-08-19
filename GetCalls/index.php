<!-- Get Calls-->
<?PHP
  //Please edit config.php before running this script
  //config.php defines username, password, and license key 
?>
<html>
    <head>
    </head>
    <body>
        <div id="container">

        <h1>Get Calls</h1>
        
        <p class="verb">This script shows how to get call history using synety cloudcall api</p>
        
        <?php

            require_once("Synety_account_api.php");

            $synety_api = new Synety_account_api();

            // parameters: offset, limit, from date, to date
            // date format: YYYY-MM-DD
            $json = $synety_api->get_calls(0, 20, "2012-09-29", "2012-10-03");

            $calls = json_decode($json, true);  

        ?>
        
        <h3>Output</h3>
        
        <p>
            Note: not all information is displayed, please see the JSON output below for all returned data.
        </p>
        
            <?php if ( count($calls) > 0 ) : ?>
            <table width="970" border="1" cellspacing="3" cellpadding="4">
                <tr>
                    <th>ID</th>
                    <th>Caller</th>
                    <th>Receiver</th>
                    <th>Account ID</th>
                    <th>Connected</th>
                    <th>Disconnected</th>
                    <th>Charged time</th>
                    <th>Charged amount</th>
                    <th>Direction</th>
                    <th>Call recording</th>
                </tr>
                <?php foreach ( $calls as $call ) : ?>
                    <tr>
                        <td><?php echo $call["id"]; ?></td>
                        <td><?php echo $call["CLI"]; ?></td>
                        <td><?php echo $call["CLD"]; ?></td>
                        <td><?php echo $call["AccountID"]; ?></td>
                        <td><?php echo $call["ConnectTime"]; ?></td>
                        <td><?php echo $call["DisconnectTime"]; ?></td>
                        <td><?php echo $call["ChargedTime"]; ?></td>
                        <td><?php echo $call["ChargedAmount"]; ?></td>
                        <td><?php echo $call["Direction"]; ?></td>
                        <td>
                            <?php if ( $call["CallRecordingAvailable"] === false ) : ?>
                                No 
                            <?php else: ?>
                                Yes
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
            No calls
            <?php endif; ?>

            <h3>JSON Output</h3>
            <code>
                <?php echo $json; ?>
            </code>
        </div>
    </body>
</html>

