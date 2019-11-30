# endpoint

// database schema

CREATE DATABASE staffRecords; 

CREATE TABLE `staffrecords`.`staffdetails` ( `staff_uid` VARCHAR(50) NOT NULL , `staff_data` JSON NOT NULL , UNIQUE `Staff Id` (`staff_uid`)) ENGINE = InnoDB;
