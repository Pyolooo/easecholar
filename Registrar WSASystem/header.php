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
			padding: 0;
		}
.content-wrapper {
    margin-top: 40px;
    min-height:800px;
    padding-bottom:90px;
}
.header-line {
    font-weight:900;
    padding-bottom:25px;
    border-bottom:1px solid #eeeeee;
    text-transform:uppercase;
}

.wrapper .multi_color_border{
  width: 100%;
  height: 5px;
  background: yellow;
}

.wrapper .top_nav{
  width: 100%;
  height: 105px;
  background: rgb(34,195,41);
  background: linear-gradient(0deg, rgba(34,195,41,1) 0%, rgba(14,72,6,1) 100%);
  padding: 0 90px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.wrapper .top_nav .left .logo p{
  margin-right: 50px;
  font-size: 20px;
  font-weight: bold;
  color: #E0C523;
  font-family: "Calibre", cursive;

}
.wrapper .top_nav .left .logo img{
  width: 100%;  
  height: 100%;
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


</style>


<body>
	
<div class="wrapper">
    <div class="top_nav">
        <div class="left">
          <div class="logo"><img src="img/headerisu.png" alt=""></div>
        </div> 
    </div>
    <div class="multi_color_border"></div>
</body>
</html>
	