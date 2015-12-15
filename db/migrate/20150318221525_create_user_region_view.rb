class CreateUserRegionView < ActiveRecord::Migration
  def up
    execute "
      create or replace view vw_UserRegion as
	  select LU.Id as UserId,
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
  end

  def down
  	execute 'drop view vw_UserRegion'
  end
end
