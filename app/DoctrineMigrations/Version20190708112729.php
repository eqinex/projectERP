<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190708112729 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE project_passport (id INT AUTO_INCREMENT NOT NULL, name LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');

        $passportNames = [
            'Приказ о создании рабочей группы',
            'Техническое задание',
            'Договор (на выполнение работ (поставки) НПО "АТ", на работы с соисполнителями) + все приложения к договору (дополнительные соглашения, календарный план-график, структура цены, протоколы и др.)',
            'ТЭО',
            'Протоколы научно-технического совета (НТС)',
            'Чертеж общего вида (ВО)',
            'Сборочный чертеж(СБ)',
            'Спецификация',
            'Монтажный чертеж. (МЧ)',
            'Чертеж деталей',
            'Электромонтажный чертеж. (МЭ)',
            'Упаковочный чертеж (УЧ)',
            'Схема (по ГОСТ 2.701)',
            'Электронная модель детали',
            'Электронная модель сборочной единицы (ЭСБ)',
            'Электронная структура изделия',
            'Перечень элементов(ПЭ)',
            'Пояснительная записка(ПЗ)',
            'Ведомость спецификаций (ВС)',
            'Ведомость покупных изделий (ВП)',
            'Ведомость эскизного проекта (ЭП)',
            'Ведомость технического проекта (ТП)',
            'Архитектурная документация программного обеспечения нижнего уровня',
            'Техническая документация программного обеспечения нижнего уровня',
            'Архитектурная документация программного обеспечения верхнего уровня',
            'Техническая документация программного обеспечения верхнего уровня',
            'Пользовательская документация программного обеспечения верхнего уровня',
            'Программа и методика испытаний',
            'Руководство по эксплуатации',
            'Паспорт на изделие',
            'Инструкция по монтажу, пуску, регулированию и обкатке изделия',
            'Ремонтные документы',
            'Ведомость ЗИП',
            'Технические условия(ТУ)',
            'Ведомость эксплуатационных документов (ВЭ)',
            'Формуляр (ФО)',
            'Этикетка (ЭТ)',
            'Приказы о назначении групп для проведения испытаний',
            'Протокол испытаний',
            'Акт испытаний',
            'Технологическая инструкция',
            'Специальные технологические процессы',
            'Технологическая карта',
            'Карта наладки',
            'Ведомость материалов',
            'Ведомость деталей (сборочных единиц) к типовому (групповому) технологическому процессу (операции)',
            'Заключение метрологической экспертизы',
            'Альбом иллюстраций (фото, видео материалы)',
            'Отчет о научно-исследовательской работе ГОСТ 7.32-2001',
            'Исследования на патентную чистоту ГОСТ Р15.011-96',
            'Заявка на патент/полезную модель/авторское свидетельство',
            'Калькуляция затрат',
            'Товарная накладная, форма торг 12',
            'Счет на оплату (аванс/расчет)',
            'Счет-фактура',
            'Расходный ордер оплаты услуг соисполнителей',
            'Протокол НТС о готовности к сдаче/приемке работ соисполнителя',
            'Документы, предусмотренные ТЗ (программы и протоколы испытаний опытного образца, акты изготовления макета, образца, проект программы приемки НИР/ОКР)',
            'Акт сдачи-приемки выполненных работ',
            'Акт приема-передачи результата работ (макета/образца)',
            'Акт сдачи-приемки выполненного этапа НИР (ОКР)',
            'Итоговый акт сдачи-приемки',
            'Акт приема – передачи',
            'Акт сборки',
            'Акт ввода в эксплуатацию',
            'Акт сверки расчетов',
            'Акт сдачи-приемки выполненных работ',
            'Регистрационная карта (РК)',
            'Информационная карта (ИК)',
            'Уведомление о готовности НИР/ОКР к приемке по форме Б.2',
            'Заключение ВП о готовности НИР/ОКР к приемке',
            'Удостоверение ВП о выполнении НИР/ОКР',
            'Заявление о соответствии по форме, установленной приказом Министра, обороны Российской Федерации 2013 года №6',
            'Акты приемки (ф.Б.3, ф.Б.5)',
            'Официальная переписка с ВП',
            'Протоколы совещаний по проекту, НТС',
            'Акты входного контроля',
            'Утвержденный дизайн-макет (АТ-159)',
            'Сертификат/Декларация',
            'Заключение метрологической экспертизы',
            'Официальная переписка (с заказчиками, соисполнителями)',
            'Диаграмма Ганта (АТ-21)',
            'Паспорт рисков (АТ-158)',
            'Модернизационный лист (АТ-18)',
            'Паспорт проекта (содержание)',
            'Прочее'
        ];

        foreach ($passportNames as $passportName) {
            $this->addSql("INSERT INTO project_passport (name) VALUES ('" . $passportName . "')");
        }

        $this->addSql('ALTER TABLE project_file ADD project_passport_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project_file ADD CONSTRAINT FK_B50EFE08633BE72B FOREIGN KEY (project_passport_id) REFERENCES project_passport (id)');
        $this->addSql('CREATE INDEX IDX_B50EFE08633BE72B ON project_file (project_passport_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE project_file DROP FOREIGN KEY FK_B50EFE08633BE72B');
        $this->addSql('DROP TABLE project_passport');
        $this->addSql('DROP INDEX IDX_B50EFE08633BE72B ON project_file');
        $this->addSql('ALTER TABLE project_file DROP project_passport_id');
    }
}
