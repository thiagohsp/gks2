import styled from 'styled-components';

export const Container = styled.div`
  display: flex;
  margin: 8px 4px;
  padding-bottom: 4px;
`;

interface ButtonProps {
    active?: boolean;
}

export const Button = styled.button<ButtonProps>`
  color: var(--dark-gray);
  float: left;
  padding: 8px 16px;
  text-decoration: none;
  transition: background-color .3s;
  border: 1px solid ${props => props.active ? "var(--light-indigo)" : "var(--light-gray)"};
  margin: 0 4px;
  border-radius: 4px;
  background-color: ${props => props.active ? "var(--light-gray)" : "white"};

  &:hover {
      background-color: var(--light-gray);
      color: var(--dark-indigo)
  }


`;
