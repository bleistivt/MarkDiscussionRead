<?php

use Garden\StaticCacheTranslationTrait;

class MarkDiscussionReadPlugin extends Gdn_Plugin {

    use StaticCacheTranslationTrait;

    public function discussionController_markRead_create($sender, $args) {
        if (!$sender->Request->isAuthenticatedPostBack()) {
            throw permissionException('Javascript');
        }

        $discussion = (new DiscussionModel())->getID(val(0, $args));
        if (!$discussion) {
            throw notFoundException('Discussion');
        }

        $count = $discussion->CountComments;
        (new CommentModel())->setWatch($discussion, $count, $count, $count);

        $sender->jsonTarget("#Discussion_{$discussion->DiscussionID}", 'New Unread', 'RemoveClass');
        $sender->jsonTarget("#Discussion_{$discussion->DiscussionID} .NewCommentCount", null, 'Remove');
        $sender->jsonTarget("#Discussion_{$discussion->DiscussionID}", 'Read', 'AddClass');

        $discussion->CountUnreadComments = 0;
        $sender->sendOptions($discussion);

        $sender->render('blank', 'utility', 'dashboard');
    }

    public function discussionsController_discussionOptionsDropdown_handler($sender, $args) {
        if (!Gdn::session()->isValid() || !$args['Discussion']->CountUnreadComments) {
            return;
        }

        $args['DiscussionOptionsDropdown']->addLink(
            self::t('Mark as read'),
            '/discussion/markread/'.$args['Discussion']->DiscussionID,
            'markread',
            'MarkRead Hijack'
       );
    }

    public function categoriesController_discussionOptionsDropdown_handler($sender, $args) {
        $this->discussionsController_discussionOptionsDropdown_handler($sender, $args);
    }

}
