$(document).ready(function() {
    MageHero_App.bindUpvote();
    MageHero_App.addNumbers();
    $('table.listing').tablesorter({
        headers: {
            0: { sorter: false },
            2: { sorter: false },
            5: { sorter: false },
            6: { sorter: false }
        },
        textExtraction: function(cell) {
            var votes = $(cell).find('.vote-count');
            if (votes.length) {
                return votes.text();
            }
            return $(cell).text();
        },
        widgets: ['numbers']
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Nothing yet
});

MageHero_App = {
    bindUpvote: function() {
        var self = this;

        $('.upvote a').click(function() {
            var userId = $(this).closest('tr').attr('data-user-id');
            var upvoteCount = $(this).closest('.upvote').find('.vote-count')

            $.ajax({
                url: '/user/' + userId + '/upvote',
                method: 'GET',
                success: function(data) {
                    console.log(data);
                    if (! data.success) {
                        alert(data.message);
                        return;
                    }

                    upvoteCount.text(data.vote_count).show();
                }
            });
        });

        return this;
    },
    addNumbers: function() {
        $.tablesorter.addWidget({
            id: 'numbers',
            format: function(table) {
                var header = table.rows[0].cells[0];
                var isColumn = (header.innerHTML == '#');
                if (!isColumn) {
                    var col = table.rows[0].insertBefore(document.createElement('th'), table.rows[0].cells[0]);
                    col.innerHTML = '#';
                    col.className += 'right';
                }
                var col;
                for (var i = 1, row; row = table.rows[i]; i++) {
                    if (isColumn) {
                        table.rows[i].cells[0].innerHTML = i + '.';
                    } else {
                        col = table.rows[i].insertCell(0);
                        col.innerHTML = i + '.';
                        col.className += ' right';
                    }
                }
            }
        });
    }
};

