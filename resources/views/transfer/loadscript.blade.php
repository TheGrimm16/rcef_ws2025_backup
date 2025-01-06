
<script>
    $(".delete_var").click(function () {
        var coop_id = $("#coop_id").val();
        var temp_transfer = $("#temp_transfer").val();
        var seed_variety = $(this).attr("for");
        var bags = $(this).attr("bags");
        var arr = coop_id + ">" + seed_variety + ">" + bags + '<';
        var replace = temp_transfer.replace(arr, "");
        $("#temp_transfer").val(replace);
        reload_transfer();
    });
</script>