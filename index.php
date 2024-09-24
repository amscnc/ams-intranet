<?php get_header();?>
<div class="main">
    <header class="header">
        <div class="whois" id="who_is">
            <form id="whois_form">
                <label for="login">Log In</label>
                <input type="text" name="emp_id" id="emp_id">
                <input type="submit" value="submit">
            </form>
        </div>
    </header>
    <div class="search">
        <form id="search_form">
            <label for="search_box">Enter Invoice Number</label>
            <input type="text" name="search_box" id="search_box">
            <input type="submit" value="Clock In">
        </form>
    </div>
    <div class="clocked-into" id="clocked_into"></div>
</div>
<?php get_footer();?>