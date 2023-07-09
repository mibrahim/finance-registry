function fill(key) {
    $.ajax({
        url: "inc/getrow.php?q=" + key,
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

function duplicate(key) {
    $.ajax({
        url: "inc/getrow.php?q=" + key,
        cache: false
    }).done(function (data) {
        var obj = JSON.parse(data);
        $('#newtxnkey').val(obj.key);
        $('#newentity').val(obj.entity);
        $('#newaccount').val(obj.account);
        $('#newstatus').val(obj.status);
        $('#newtarget').val(obj.target);
        $('#newamount').val(obj.amount);
        $('#newdescription').val(obj.description);
        $('#newdate').val(obj.date);
        $('#neword').val(obj.ord);
        $('#newnotes').val(obj.notes);
        $('#newurl').val(obj.url);
    });
}

function jump(id) {
    window.addEventListener("load", function () {
        console.log("Jumpting to " + id);
        var top = document.getElementById(id).offsetTop; //Getting Y of target element
        window.scrollTo(0, top);                        //Go there directly or some transition
    });
}
