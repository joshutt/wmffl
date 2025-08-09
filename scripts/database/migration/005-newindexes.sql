create index transactions_Date_index
    on transactions (Date);

create index date_index
    on weekmap (StartDate, EndDate);

create index enddate_idx
    on weekmap (EndDate);