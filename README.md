**freeCodeCamp** - Information Security and Quality Assurance Project
------

**Metric-Imperial Converter**

### User Stories:

1. I can **GET** `/api/convert` with a single parameter containing an accepted number and unit and have it converted. Hint: Split the input by looking for the index of the first character which will mark the start of the unit.
2. I can convert 'gal' to 'L' and vice versa. **(1 gal to 3.78541 L)**
3. I can convert 'lbs' to 'kg' and vice versa. **(1 lbs to 0.45359 kg)**
4. I can convert 'mi' to 'km' and vice versa. **(1 mi to 1.60934 km)**
5. If my number is invalid, returned will be 'invalid number'.
6. If my unit of measurement is invalid, returned will be 'invalid unit'.
7. If both are invalid, returned will be 'invalid number and unit'.
8. I can use fractions, decimals or both in my parameter (for example: 5, 1/2, 2.5/6), but if nothing is provided it will default to 1.
9. Returned will consist of the initNum, initUnit, returnNum, returnUnit, and string spelling out units in format `{initNum} {initial_Units} converts to {returnNum} {return_Units}` with the result rounded to 5 decimals.
10. All 21 tests are complete and passing.