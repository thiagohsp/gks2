import React, { ButtonHTMLAttributes } from 'react';
import cx from 'classnames';

interface LoadingButtonProps extends ButtonHTMLAttributes<HTMLButtonElement>{
    loading: boolean;
    className: string;
}

const LoadingButton: React.FC<LoadingButtonProps> = ({ loading, className, children, ...props }) => {
  const classNames = cx(
    'flex items-center',
    'focus:outline-none',
    {
      'pointer-events-none bg-opacity-75 select-none': loading
    },
    className
  );
  return (
    <button disabled={loading} className={classNames} {...props}>
      {loading && <div className="mr-2 btn-spinner" />}
      {children}
    </button>
  );
};

export default LoadingButton;
