$(document).ready(function() {
    MageHero_App.bindUpvote();
    MageHero_App.bindPostUpvote();
    MageHero_App.bindChosen();
    MageHero_App.setupEpicEditor();
    MageHero_App.bindProgressiveLoad();
    new LazyLoader('.lazyload', 'data-src');
});

document.addEventListener('DOMContentLoaded', function() {
    // Nothing yet
});

MageHero_App = {
    currentUserLimit: null,
    usersPerPage: 20,

    bindUpvote: function() {
        var self = this;

        $('.logged-in .upvote a').click(function(e) {
            e.preventDefault();

            var userId = $(this).closest('tr').attr('data-user-id');
            var upvoteCount = $(this).closest('.upvote').find('.vote-count')

            $.ajax({
                url: '/user/' + userId + '/upvote',
                method: 'GET',
                success: function(data) {
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

    bindPostUpvote: function() {
        var self = this;

        $('.logged-in .post-upvote-link').click(function(e) {
            e.preventDefault();

            var postId = $(this).closest('.post-upvote').attr('data-post-id');
            var upvoteCount = $(this).closest('.post-upvote').find('.post-vote-count');

            $.ajax({
                url: '/posts/' + postId + '/upvote',
                method: 'GET',
                success: function(data) {
                    if (! data.success) {
                        alert(data.message);
                        return;
                    }

                    upvoteCount.text(data.vote_count);
                }
            });
        });

        return this;
    },

    bindChosen: function() {
        $('.fancy-select').chosen();
        $('.tooltip').tooltipster();
    },

    setupEpicEditor: function() {
        if ($('textarea.body').size()) {
            var opts = {
                textarea: 'body',
                theme: {
                    base:       '../../../../epiceditor/themes/base/epiceditor.css',
                    preview:    '../../../../epiceditor/themes/preview/preview-dark.css',
                    editor:     '../../../../epiceditor/themes/editor/epic-dark.css'
                }
            };
            var editor = new EpicEditor(opts).load();
        }
    },

    bindProgressiveLoad: function() {
        var self = this;

        $('.load-more a').click(function() {
            self.handleProgressiveLoad();
        });
    },

    handleProgressiveLoad: function() {
        var self = this;
        this.currentUserLimit += this.usersPerPage;

        $('.load-more a').text('Loading...');

        $.ajax({
            url: '/users-fragment/' + self.currentUserLimit,
            method: 'GET',

            success: function(data) {
                if (! data.success) {
                    alert("Uh-oh, there was a problem!");
                    return;
                }

                if (data.html) {
                    $('.listing').append(data.html);
                }

                if (data.count > self.usersPerPage) {
                    $('.load-more a').text('Load More');
                } else {
                    $('.load-more a').hide();
                }
            }
        });
    }
};
