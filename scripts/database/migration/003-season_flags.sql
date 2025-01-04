create table season_flags
(
    id              int auto_increment,
    season          year           not null,
    teamid          int            not null,
    flags           varchar(3)     null,
    division_winner bool default 0 not null,
    playoff_team    bool default 0 not null,
    finalist        bool default 0 not null,
    champion        bool default 0 not null,
    constraint season_flags_pk
        primary key (id),
    constraint season_team_key
        unique (season, teamid),
    constraint season_flags_team_TeamID_fk
        foreign key (teamid) references team (TeamID)
) ENGINE = InnoDB
  CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

alter table team
    engine =InnoDB;

alter table team
    collate = utf8mb4_unicode_ci;

alter table team
    charset = utf8mb4;

INSERT INTO season_flags (id, season, teamid, flags, division_winner, playoff_team, finalist, champion)
VALUES (1, 2024, 1, 'e', 0, 0, 0, 0);
INSERT INTO season_flags (id, season, teamid, flags, division_winner, playoff_team, finalist, champion)
VALUES (2, 2024, 2, 't', 0, 0, 0, 0);
INSERT INTO season_flags (id, season, teamid, flags, division_winner, playoff_team, finalist, champion)
VALUES (3, 2024, 3, 'e', 0, 0, 0, 0);
INSERT INTO season_flags (id, season, teamid, flags, division_winner, playoff_team, finalist, champion)
VALUES (4, 2024, 4, 'y', 1, 1, 1, 0);
INSERT INTO season_flags (id, season, teamid, flags, division_winner, playoff_team, finalist, champion)
VALUES (5, 2024, 5, 'e', 0, 0, 0, 0);
INSERT INTO season_flags (id, season, teamid, flags, division_winner, playoff_team, finalist, champion)
VALUES (6, 2024, 6, 'e', 0, 0, 0, 0);
INSERT INTO season_flags (id, season, teamid, flags, division_winner, playoff_team, finalist, champion)
VALUES (7, 2024, 7, 'e', 0, 0, 0, 0);
INSERT INTO season_flags (id, season, teamid, flags, division_winner, playoff_team, finalist, champion)
VALUES (8, 2024, 8, 't', 0, 0, 0, 0);
INSERT INTO season_flags (id, season, teamid, flags, division_winner, playoff_team, finalist, champion)
VALUES (9, 2024, 9, 'e', 0, 0, 0, 0);
INSERT INTO season_flags (id, season, teamid, flags, division_winner, playoff_team, finalist, champion)
VALUES (10, 2024, 10, 'y', 1, 1, 0, 0);
INSERT INTO season_flags (id, season, teamid, flags, division_winner, playoff_team, finalist, champion)
VALUES (11, 2024, 12, 'y', 1, 1, 0, 0);
INSERT INTO season_flags (id, season, teamid, flags, division_winner, playoff_team, finalist, champion)
VALUES (12, 2024, 13, 'x', 0, 1, 1, 0);
