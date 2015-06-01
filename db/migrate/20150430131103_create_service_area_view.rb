class CreateServiceAreaView < ActiveRecord::Migration
  def up
    execute "
      create or replace view vw_ServiceArea as
      select SA.RegionalId as RegionalId,
        S.Id as StateId,
        coalesce(C1.Id, C2.Id) AS CityId,
        coalesce(N1.Id, N2.Id, N3.Id) AS NeighborhoodId
      from ServiceArea SA
      inner join Regional R on R.Id = SA.RegionalId and R.Status = 'A'
      left join State S on S.Id = SA.StateId
      left join City C1 ON C1.Id = SA.CityId
      left join City C2 ON C2.StateId = S.Id
      left join Neighborhood N1 ON N1.Id = SA.NeighborhoodId
      left join Neighborhood N2 ON N2.CityId = C1.Id
      left join Neighborhood N3 ON N3.CityId = C2.Id
    "

    execute '
      create or replace view vw_UserRegion as
      select LU.Id as UserId,
        LSA.RegionalId as RegionalId,
        LSA.StateId as StateId,
        LSA.CityId as CityId,
        LSA.NeighborhoodId as NeighborhoodId
      from User as LU
      inner join UserLocality as LUL on LUL.UserId = LU.Id
      inner join vw_ServiceArea as LSA on LSA.RegionalId = LUL.RegionalId
    '
  end

  def down
    execute "
      create or replace view vw_UserRegion as
      select LU.Id as UserId,
        LSA.RegionalId as RegionalId,
        LS.Id as StateId,
        COALESCE(LC1.Id, LC2.Id) as CityId,
        COALESCE(LN1.Id, LN2.Id, LN3.Id) as NeighborhoodId
      from `User` AS `LU`
      INNER JOIN `UserLocality` AS `LUL` ON LUL.UserId = LU.Id
      INNER JOIN `Regional` AS `LR` ON LR.Id = LUL.RegionalId and LR.Status = 'A'
      INNER JOIN `ServiceArea` AS `LSA` ON LSA.RegionalId = LR.Id
      LEFT JOIN `State` AS `LS` ON LS.Id = LSA.StateId
      LEFT JOIN `City` AS `LC1` ON LC1.Id = LSA.CityId
      LEFT JOIN `City` AS `LC2` ON LC2.StateId = LS.Id
      LEFT JOIN `Neighborhood` AS `LN1` ON LN1.Id = LSA.NeighborhoodId
      LEFT JOIN `Neighborhood` AS `LN2` ON LN2.CityId = LC1.Id
      LEFT JOIN `Neighborhood` AS `LN3` ON LN3.CityId = LC2.Id
    "

    execute 'drop view vw_ServiceArea'
  end
end