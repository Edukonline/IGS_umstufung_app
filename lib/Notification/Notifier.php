<?php
namespace OCA\KursUmstufung\Notification;

use OCA\KursUmstufung\Constants\RequestStatus;
use OCA\KursUmstufung\Service\NotificationService;
use OCP\IURLGenerator;
use OCP\L10N\IFactory;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;

class Notifier implements INotifier {
    private IFactory $l10nFactory;
    private IURLGenerator $url;

    public function __construct(IFactory $l10nFactory, IURLGenerator $url) {
        $this->l10nFactory = $l10nFactory;
        $this->url = $url;
    }

    public function getID(): string {
        return NotificationService::APP_ID;
    }

    public function getName(): string {
        return 'KursUmstufung';
    }

    public function prepare(INotification $notification, string $languageCode): INotification {
        if ($notification->getApp() !== NotificationService::APP_ID) {
            // \InvalidArgumentException ist über alle NC-Versionen 25–35 der
            // dokumentierte Weg, eine unbekannte Notification abzulehnen.
            throw new \InvalidArgumentException();
        }

        $l = $this->l10nFactory->get(NotificationService::APP_ID, $languageCode);
        $params = $notification->getSubjectParameters();
        $iconUrl = $this->url->getAbsoluteURL($this->url->imagePath(NotificationService::APP_ID, 'app-dark.svg'));
        $notification->setIcon($iconUrl);

        switch ($notification->getSubject()) {
            case NotificationService::SUBJECT_SUBMITTED:
                $count = (int)($params['count'] ?? 0);
                $message = $l->n(
                    '%n neuer Umstufungsantrag wurde zur Prüfung eingereicht.',
                    '%n neue Umstufungsanträge wurden zur Prüfung eingereicht.',
                    $count
                );
                $notification->setParsedSubject($message);
                $notification->setLink($this->url->linkToRouteAbsolute('kursumstufung.page.index'));
                return $notification;

            case NotificationService::SUBJECT_DECIDED:
                $student = (string)($params['student'] ?? '');
                $approved = ($params['decision'] ?? '') === RequestStatus::APPROVED;
                $message = $approved
                    ? $l->t('Ihr Umstufungsantrag für %s wurde genehmigt.', [$student])
                    : $l->t('Ihr Umstufungsantrag für %s wurde abgelehnt.', [$student]);
                $notification->setParsedSubject($message);
                $notification->setLink($this->url->linkToRouteAbsolute('kursumstufung.page.index'));
                return $notification;

            default:
                // \InvalidArgumentException ist über alle NC-Versionen 25–35 der
            // dokumentierte Weg, eine unbekannte Notification abzulehnen.
            throw new \InvalidArgumentException();
        }
    }
}
