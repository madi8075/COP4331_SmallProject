<!DOCTYPE html>
<html>
  <head>
    <meta charset = "UTF-8">
    <title>Starfinder</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
  </head>

  <body>
    <video autoplay muted loop plays-inline class="background-video">
        <source src="images/nebula.mp4" type="video/mp4">
    </video>
    <div class="main">
        <button style="right: 10px; top: 5px;"class="glow-on-hover">Sign Out</button>
        <h1 class="floating glowing title">Contacts</h1>
        <button style="left:10px; top: 5px;"class="glow-on-hover">Add New Contact</button>
        <div class="box">
            <table class="contact-table">
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <!--<th>Date Added</th>-->
                    <th>View</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
                <tr>
                    <td>Jon Smith</td>
                    <td>phone</td>
                    <td><button class="viewButton"><img src="images/magGlass.png" class="magGlass"></button></td>
                    <td><button class="viewButton"><img src="images/pencil1.png" class="magGlass"></button></td>
                    <td><button class="viewButton"><img src="images/trash.gif" class="magGlass"></button></td>
                </tr>
                <tr></tr>
                <td>Rick Astley</td>
                <td>33333333333333333333333</td>
                <td><button class="viewButton"><img src="images/magGlass.png" class="magGlass"></button></td>
                <td><button class="viewButton"><img src="images/pencil1.png" class="magGlass"></button></td>
                <td><button class="viewButton"><img src="images/trash.gif" class="magGlass"></button></td>
                <tr>                
                    <td>John Cena</td>
                    <td>123456789</td>
                    <td><button class="viewButton"><img src="images/magGlass.png" class="magGlass"></button></td>
                    <td><button class="viewButton"><img src="images/pencil1.png" class="magGlass"></button></td>
                    <td><button class="viewButton"><img src="images/trash.gif" class="magGlass"></button></td></tr>
            </table>
        </div>
    </div>
    </body>
</html>
