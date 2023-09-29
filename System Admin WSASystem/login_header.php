<style>
/* CSS styles for login_header.php */
body {
  margin: 0;
  padding: 0;
}

.content-wrapper {
  margin-top: 40px;
  min-height: 800px;
  padding-bottom: 90px;
  position: sticky;
}

.header-line {
  font-weight: 900;
  padding-bottom: 25px;
  border-bottom: 1px solid #eeeeee;
  text-transform: uppercase;
}

.wrapper .multi_color_border {
  width: 100%;
  height: 5px;
  background: yellow;
}

.wrapper .top_nav {
  width: 100%;
  height: 105px;
  background: rgb(34, 195, 41);
  background: linear-gradient(0deg, rgba(34, 195, 41, 1) 0%, rgba(14, 72, 6, 1) 100%);
  padding: 0 90px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.wrapper .top_nav .left .logo p {
  margin-right: 50px;
  font-size: 20px;
  font-weight: bold;
  color: #e0c523;
  font-family: "Calibre", cursive;
}

.wrapper .top_nav .left .logo img {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  color: #e0c523;
}

</style>

<div class="wrapper">
  <div class="top_nav">
    <div class="left">
      <div class="logo"><img src="headerisu.png" alt=""></div>
    </div>
  </div>
  <div class="multi_color_border"></div>
</div>
