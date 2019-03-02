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

function addAccount() {
    // Get the account groups
    $.ajax({
        url: "/apis/getaccountgroups.php",
    }).done(function (data) {
        if (console && console.log) {
            let myArray = JSON.parse(data);
            let radioButtons = "";
            let spaces = "**********************************************";
            for (let i = 0; i != myArray.length; i++) {
                let nbspSpaces = spaces.substr(0, myArray[i].indent).replace("*", "&nbsp;&nbsp;");
                radioButtons += nbspSpaces +
                    '<input type="radio" name="group" value="' + myArray[i].id + '">' +
                    myArray[i].name + "<br/>";
            }

            $('<div></div>').dialog({
                modal: true,
                width: 600,
                height: 500,
                title: "Add an account",
                open: function () {
                    var markup = '<form method="post">' +
                        '<input type="hidden" name="todo" value="addaccount"/> ' +
                        '<table><tr><td><b>Name</b></td><td> <input type="text" name="name"/></td></tr>' +
                        '<tr><td><b>Description</b></td><td> <input type="text" name="description"/></td></tr>' +
                        '<tr><td colspan="2"><br/><b>Account Group:</b><br/><br/>' +
                        radioButtons +
                        '</td></tr>' +
                        '<tr><td colspan="2" style="text-align: center;"><input type="submit" value="Add Account"/></td></tr>' +
                        '</table>'
                    '</form>';
                    $(this).html(markup);
                },
            });  //end confirm dialog
        }
    });
}

function favorite(accountId) {
    let ajaxURL = "/apis/favorite.php?favorite=" + accountId;
    $.ajax({
        url: ajaxURL,
    }).done(function (data) {
        console.log(ajaxURL);
        console.log(data);
        location.reload();
    });
}