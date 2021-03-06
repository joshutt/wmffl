create table comments
(
  comment_id int auto_increment
    primary key,
  article_id int not null,
  comment_text text not null,
  author_id int not null,
  date_created timestamp default CURRENT_TIMESTAMP not null,
  active tinyint(1) default '0' null,
  parent_id int null,
  constraint comments_comments_comment_id_fk
  foreign key (parent_id) references comments (comment_id)
)
;

create index comments_article_id_date_created_index
  on comments (article_id, date_created)
;

create index comments_comment_id_index
  on comments (comment_id)
;

create index comments_parent_id_date_created_index
  on comments (parent_id, date_created)
;
