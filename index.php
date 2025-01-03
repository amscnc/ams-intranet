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
    <div class="clock">
        <form id="clock_form">
            <label for="invoice_box">Enter Invoice Number</label>
            <input type="text" name="invoice_box" id="invoice_box">
            <select name="work_type" id="work_type">
                <option selected value>General</option>
                <option value="block">Block</option>
                <option value="block bore">Block Bore</option>
                <option value="block hone">Block Hone</option>
                <option value="line bore">Line Bore</option>
                <option value="line hone">Line Hone</option>
                <option value="crank">Crank</option>
                <option value="crank grind">Crank Grind</option>
                <option value="crank polish">Crank Polish</option>
                <option value="head">Head</option>
                <option value="rods">Rods</option>
                <option value="assemble engine">Assemble Engine</option>
            </select>
            <input type="submit" value="Clock In">
        </form>
    </div>
    <div class="jobs" id="clocked_into"></div>
</div>
<?php get_footer();?>