<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Double Navigation Bar</title>
	<link rel="stylesheet" href="">

</head>
  <style>
body {
			margin: 0;
		}
.wrapper {
display: flex;
flex-direction: column;
padding-bottom: 110px;
}
.header-line {
    font-weight:900;
    padding-bottom:25px;
    border-bottom:1px solid #eeeeee;
    text-transform:uppercase;
}

.top_nav{
position: fixed;
left: 0;
right: 0;
  height: 105px;
  background: rgb(34,195,41);
  background: linear-gradient(0deg, rgba(34,195,41,1) 0%, rgba(14,72,6,1) 100%);
  padding: 0 90px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  z-index: 100;
}

.multi_color_border{
  position: fixed;
  top: 105px;
  right: 0;
  left: 0;
  bottom: 0;
  height: 5px;
  background: yellow;
  z-index: 99;
}

.wrapper .top_nav .left .logo img{
  width: 600px;  
  height: 100px;
  display: flex;
  align-items: center;
  color: #E0C523;

}

.wrapper .top_nav .right ul{
  display: flex;
}

.wrapper .top_nav .right ul li{
  margin: 0 12px;
}

.wrapper .top_nav .right ul li:last-child{
  background: #D14D4D;
  margin-right: 0;
  border-radius: 2px;
  text-transform: uppercase;
  letter-spacing: 3px;
}

.wrapper .top_nav .right ul li:hover:last-child{
  background: #8F3A3A;
}

.wrapper .top_nav .right ul li a{
  display: block;
  padding: 8px 10px;
  color: #37a000;
}

.wrapper .top_nav .right ul li:last-child a{
   color: #fff;
}
@media screen and (max-width: 768px) {
  .wrapper .top_nav .left .logo img{
    width: 500px;  
    height: 70px;
  }
  .top_nav{
  padding: 0;

  }
}
@media screen and (max-width: 700px) {
  .wrapper .top_nav .left .logo img{
    width: 380px;  
    height: 68px;
  }
  .top_nav{
  padding: 0;
  }
}


</style>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
<div class="wrapper">
    <div class="top_nav">
        <div class="left">
          <div class="logo"><img src="/EASE-CHOLAR/headerisu.png" alt=""></div>
        </div> 
        <div class="multi_color_border"></div>
    </div>
</div>
  
</body>
</html>
	