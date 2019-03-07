function fill(key) {
    $.ajax({
        url: "/inc/getrow.php?q=" + key,
        cache: false
    }).done(function (data) {
        var obj = JSON.parse(data);
        $('#frmtxnkey').val(obj.key);
        $('#frmentity').val(obj.entity);
        $('#frmaccount').val(obj.account);
        $('#frmstatus').val(obj.status);
        $('#frmtarget').val(obj.target);
        $('#frmamount').val(obj.amount);
        $('#frmdescription').val(obj.description);
        $('#frmdate').val(obj.date);
        $('#frmord').val(obj.ord);
        $('#frmnotes').val(obj.notes);
        $('#frmurl').val(obj.url);
    });
}