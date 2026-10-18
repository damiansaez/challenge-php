CREATE  TABLE  IF  NOT  EXISTS  productos (
    id           INT  NOT  NULL  AUTO_INCREMENT ,
    nombre       VARCHAR ( 255 )  NOT  NULL ,
    descripcion  TEXT  NULL ,
    precio       DECIMAL ( 10 , 2 )  NOT  NULL ,
    created_at   TIMESTAMP  NOT  NULL  DEFAULT  current_timestamp ,
    updated_at   TIMESTAMP  NOT  NULL  DEFAULT  current_timestamp  ON  UPDATE  CURRENT_TIMESTAMP ,
    PRIMARY  KEY  ( id )  )  ENGINE = INNODB  DEFAULT  CHARSET = utf8mb4  COLLATE = utf8mb4_unicode_ci ;