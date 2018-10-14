function addSubAccountGroup(parentAccountId, nameEscaped) {
    $('<div></div>').dialog({
        modal: true,
        title: "Add a sub-group to " + nameEscaped,
        open: function () {
            var markup = '<form method="post">' +
                '<input type="hidden" name="todo" value="addsubgroup"/> ' +
                '<input type="hidden" name="parentid" value="' + parentAccountId + '"/> ' +
                'Group name <input type="text" name="name"/> <br/><br/>' +
                '<center><input type="submit" value="Add Subgroup"/></center>' +
                '</form>';
            $(this).html(markup);
        },
    });  //end confirm dialog
}