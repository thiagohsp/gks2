import styled from "styled-components";

// <table>
export const TableContainer = styled.table`
  /* enable scroll on small width */
  //display: block;
  width: 100%;
  border-collapse: collapse;
`;

export const Th = styled.th`
  //min-width: 142px;
  padding: 12px;
  border-bottom: 2px solid #cbd5e0;
  font-size: 1rem;
  text-align: left;
`;

export const Td = styled.td`
  padding: 10px;
  text-align: left;
  &:nth-of-type(1) {
    text-align: left;
    /* make first column body sticky */
    left: 0;
    position: sticky;
  }

`;

export const Tr = styled.tr`
  &:nth-of-type(odd) td {
    background-color: #F8F8FF;
  }
  &:nth-of-type(even) td {
    background-color: #F5F5F5;
  }
`;

export const TrHead = styled.tr`
  padding: 0px 27px;
`;

export const HeaderCheckbox = styled.input.attrs({ type: 'checkbox' })`
      background-color: #000;
      font-size: '1rem';
      &:hover {
        background-color: 'transparent';
      };
`;

export const RowCheckbox = styled.input.attrs({ type: 'checkbox' })`
      background-color: #000;
      font-size: '1rem';
      &:hover {
        background-color: 'transparent';
      };
`;

