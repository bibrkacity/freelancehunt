<?php
class import
{

    const TOKEN = '2d2c1ca2ac209b10274b5a71eb860b72012babf2';

    public function content(): string
    {

        if (count($_POST) == 0)
            $content = $this->form();
        else
            $content = $this->result();

        return $content;
    }


    private function form(): string
    {
        return view::render('import-form', null);

    }

    private function result(): string
    {
        try {
            $json = $this->getFromApi();

            $n = $this->save($json);

            return 'Готово. Обработано проектов: ' . $n;

        } catch (\Exception $e) {
            return $e->getMessage() . '<br />in ' . $e->getFile() . '<br />line ' . $e->getLine();
        }

    }

    private function getFromApi(): bool|string
    {
        $ch = curl_init();


        curl_setopt($ch, CURLOPT_URL, "https://api.freelancehunt.com/v2/projects");
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $authorization = "Authorization: Bearer " . self::TOKEN;
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $json = curl_exec($ch);
        curl_close($ch);

        return $json;

        //


    }

    private function save(string $json): int
    {

        global $conn;
        $projects = json_decode($json);

        $this->create_tables();

        $n = 0;

        foreach ($projects->data as $project) {
            $n++;

            $employee_id = (int)$project->attributes->employer->id;
            $employee_first_name = mysqli_real_escape_string($conn, $project->attributes->employer->first_name);
            $employee_last_name = mysqli_real_escape_string($conn, $project->attributes->employer->last_name);
            $employee_login = mysqli_real_escape_string($conn, $project->attributes->employer->login);

            $query = "INSERT IGNORE INTO `employees` (`id`,`first_name`,`last_name`, `login`) 
                         VALUES('$employee_id','$employee_first_name','$employee_last_name','$employee_login')
                      ON DUPLICATE KEY UPDATE `first_name`='$employee_first_name', `last_name`='$employee_last_name'
                     ";

            mysqli_query($conn, $query);

            $skills = $project->attributes->skills;

            $skills_in_project = [];

            foreach ($skills as $skill) {
                $skill_id = (int)$skill->id;
                $skill_name = mysqli_real_escape_string($conn, $skill->name);

                $query = "INSERT IGNORE INTO `skills` (`id`,`name`) 
                         VALUES('$skill_id','$skill_name')
                      ON DUPLICATE KEY UPDATE `name`='$skill_name'
                    ";
                mysqli_query($conn, $query);

                $skills_in_project[] = $skill_id;
            }

            $id = $project->id;
            $name = mysqli_real_escape_string($conn, $project->attributes->name);
            $url = mysqli_real_escape_string($conn, $project->links->self->web);
            $budget = common::toNumber($project->attributes->budget->amount);
            $currency = mysqli_real_escape_string($conn, $project->attributes->budget->currency);

            $query = "INSERT IGNORE INTO `projects` (`id`,`name`,`url`, `budget`, `currency`, `employee_id`) 
                         VALUES('$id','$name','$url','$budget','$currency', $employee_id)
                      ON DUPLICATE KEY UPDATE `name`='$name', `url`='$url', `budget`='$budget'
                    ";
            mysqli_query($conn, $query);

            foreach ($skills_in_project as $skill_id) {
                $query = "INSERT IGNORE INTO `project_skill` (`project_id`,`skill_id`) 
                         VALUES('$id','$skill_id')
                    ";
                mysqli_query($conn, $query);
            }


        }
        return $n;
    }

    private function create_tables(): void
    {
        global $conn;

        $query = <<<QWE
CREATE TABLE IF NOT EXISTS `employees` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(70) NOT NULL,
  `login` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
QWE;
        mysqli_query($conn, $query);

        $query = <<<WXC
CREATE TABLE IF NOT EXISTS `skills` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
WXC;
        mysqli_query($conn, $query);

        $query = <<<MXC
CREATE TABLE IF NOT EXISTS `projects` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `url` text NOT NULL,
  `budget` decimal(12,2) NOT NULL,
  `currency` varchar(3) NOT NULL,
  `employee_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
MXC;
        mysqli_query($conn, $query);


        $query = <<<DAC
CREATE TABLE IF NOT EXISTS `project_skill` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int unsigned NOT NULL,
  `skill_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_id` (`project_id`,`skill_id`),
  KEY `skill_id` (`skill_id`),
  CONSTRAINT `project_skill_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  CONSTRAINT `project_skill_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
DAC;
        mysqli_query($conn, $query);


    }
}
